<?php

namespace App\Http\Controllers\Api;

use App\Exports\MonitoringJobsExport;
use App\Http\Controllers\Controller;
use App\Models\DailyJob;
use App\Models\RootCauseCategories;
use App\Models\RootCauseProblem;
use App\Models\User;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelWriter;

class OperationsApiController extends Controller
{
    private const ROLE_CREATE_JOB  = ['ict_developer', 'ict_group_leader'];
    private const ROLE_UPDATE_JOB  = ['ict_developer', 'ict_group_leader', 'ict_admin', 'ict_technician'];
    private const ROLE_DELETE_JOB  = ['ict_developer', 'ict_group_leader'];
    private const ROLE_APPROVE_JOB = ['ict_group_leader'];
    private const ROLE_ALL_WRITE   = ['ict_developer', 'ict_group_leader', 'ict_section_head', 'ict_admin', 'ict_technician'];

    // -------------------------------------------------------------------------
    // Job Assignment
    // -------------------------------------------------------------------------

    public function jobsIndex(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);
        $shift = $this->normalizeShift($request->input('shift'));

        $today = Carbon::today();
        $user = $request->user();
        $query = DailyJob::with('creator')
            ->when($request->filled(['start_date', 'end_date']), function ($builder) use ($request) {
                $builder->whereBetween('date', [$request->string('start_date'), $request->string('end_date')]);
            }, function ($builder) use ($today) {
                $builder->where(function ($query) use ($today) {
                    $query->whereDate('date', $today)
                        ->orWhere(function ($subQuery) use ($today) {
                            $subQuery->where('status', '!=', 'closed')
                                ->whereDate('date', '!=', $today);
                        });
                });
            })
            ->when($shift !== null, fn ($builder) => $builder->where('shift', $shift))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->when(
                ! in_array($user->role, ['ict_developer', 'ict_group_leader', 'ict_ho'], true),
                fn ($builder) => $builder->whereJsonContains('crew', $user->id)
            )
            ->whereIn('category_job', ['assignment', 'support'])
            ->where('site', $site)
            ->orderByDesc('created_at');

        return response()->json([
            'data' => $query->paginate((int) $request->integer('per_page', 25)),
            'meta' => [
                'site' => $site,
                'users' => User::query()->select('id', 'name')->orderBy('name')->get(),
                'shift_options' => $this->shiftOptions(),
                'filters' => array_merge(
                    $request->only(['start_date', 'end_date', 'status']),
                    ['shift' => $shift]
                ),
                'can_create' => in_array($user->role, self::ROLE_CREATE_JOB, true),
                'can_approve' => in_array($user->role, self::ROLE_APPROVE_JOB, true),
            ],
        ]);
    }

    public function jobsMeta(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);

        return response()->json([
            'site' => $site,
            'users' => $this->siteCrewOptions($site),
            'shift_options' => $this->shiftOptions(),
            'categories' => RootCauseCategories::where('site_type', 'SITE')
                ->pluck('category_root_cause'),
        ]);
    }

    public function jobsStore(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRole($request, self::ROLE_CREATE_JOB);
        $this->authorizeSiteAccess($request, $site);

        $validated = $request->validate([
            'shared.shift'    => ['nullable', 'string', 'in:SHIFT_1,SHIFT_2,pagi,malam'],
            'shared.crew'     => ['nullable', 'array'],
            'shared.crew.*'   => ['nullable'],
            'shared.sarana'   => ['nullable', 'string'],
            'jobs'            => ['required', 'array', 'min:1'],
            'jobs.*.code'     => ['nullable', 'string'],
            'jobs.*.categoryJob' => ['nullable', 'string', 'in:assignment,support'],
            'jobs.*.job'      => ['required', 'string'],
            'jobs.*.date'     => ['nullable', 'date'],
            'jobs.*.due_date' => ['nullable', 'date'],
            'jobs.*.status'   => ['nullable', 'string', 'in:open,continue,closed'],
            'jobs.*.remark'   => ['nullable', 'string'],
        ]);

        foreach ($validated['jobs'] as $job) {
            DailyJob::create([
                'code'         => $job['code'] ?? null,
                'category_job' => $job['categoryJob'] ?? 'assignment',
                'description'  => $job['job'],
                'date'         => $job['date'] ?? now()->toDateString(),
                'due_date'     => $job['due_date'] ?? null,
                'status'       => $job['status'] ?? 'open',
                'remark'       => $job['remark'] ?? null,
                'shift'        => $this->normalizeShift(data_get($validated, 'shared.shift', 'SHIFT_1')),
                'crew'         => data_get($validated, 'shared.crew', []),
                'sarana'       => data_get($validated, 'shared.sarana'),
                'site'         => $site,
                'category'     => '-',
                'created_by'   => $request->user()->id,
            ]);
        }

        return response()->json(['message' => 'Jobs created successfully.'], 201);
    }

    public function jobsShow(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);

        $job = DailyJob::with(['creator', 'updater'])
            ->where('site', $site)
            ->where('code', $code)
            ->whereIn('category_job', ['assignment', 'support'])
            ->firstOrFail();

        return response()->json(['data' => $job]);
    }

    public function jobsUpdate(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRole($request, self::ROLE_UPDATE_JOB);
        $this->authorizeSiteAccess($request, $site);

        $job = DailyJob::where('site', $site)
            ->where('code', $code)
            ->whereIn('category_job', ['assignment', 'support'])
            ->firstOrFail();

        if ($job->is_approved) {
            abort(403, 'Job sudah di-approve dan tidak bisa diedit.');
        }

        $validated = $request->validate([
            'description'    => ['required', 'string'],
            'remark'         => ['required', 'string'],
            'due_date'       => ['required', 'date'],
            'status'         => ['required', 'string', 'in:open,continue,closed'],
            'category_job'   => ['required', 'string', 'in:assignment,support'],
            'crew'           => ['array'],
            'sarana'         => ['required', 'string'],
            'shift'          => ['required', 'string', 'in:SHIFT_1,SHIFT_2,pagi,malam'],
            'action_taken'   => ['nullable', 'string'],
            'start_progress' => ['nullable', 'date'],
            'end_progress'   => ['nullable', 'date'],
            'category'       => ['nullable', 'string'],
            'root_cause'     => ['nullable', 'string'],
        ]);

        $validated['shift'] = $this->normalizeShift($validated['shift']);
        $validated['updated_by'] = $request->user()->id;
        $job->update($validated);

        return response()->json(['message' => 'Job updated successfully.', 'data' => $job->fresh()]);
    }

    public function jobsDestroy(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRole($request, self::ROLE_DELETE_JOB);
        $this->authorizeSiteAccess($request, $site);

        $job = DailyJob::where('site', $site)
            ->where('code', $code)
            ->whereIn('category_job', ['assignment', 'support'])
            ->firstOrFail();

        if ($job->is_approved) {
            abort(403, 'Job sudah di-approve dan tidak bisa dihapus.');
        }

        $job->delete();

        return response()->json(['message' => 'Job deleted successfully.']);
    }

    // -------------------------------------------------------------------------
    // Approval
    // -------------------------------------------------------------------------

    public function approveJob(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRole($request, self::ROLE_APPROVE_JOB);
        $this->authorizeSiteAccess($request, $site);

        $job = DailyJob::where('site', $site)->where('code', $code)->firstOrFail();

        if ($job->is_approved) {
            return response()->json(['message' => 'Job sudah di-approve sebelumnya.']);
        }

        $job->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json(['message' => 'Job berhasil di-approve.', 'data' => $job->fresh()]);
    }

    public function approveBatch(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRole($request, self::ROLE_APPROVE_JOB);
        $this->authorizeSiteAccess($request, $site);

        $shift = $this->normalizeShift($request->input('shift'));
        $today = Carbon::today();

        $query = DailyJob::where('site', $site)
            ->where('is_approved', false)
            ->when($request->filled(['start_date', 'end_date']), function ($builder) use ($request) {
                $builder->whereBetween('date', [$request->string('start_date'), $request->string('end_date')]);
            }, function ($builder) use ($today) {
                $builder->whereDate('date', $today);
            })
            ->when($shift !== null, fn ($builder) => $builder->where('shift', $shift))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')));

        $count = $query->count();

        $query->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return response()->json(['message' => "$count job berhasil di-approve."]);
    }

    // -------------------------------------------------------------------------
    // Monitoring Jobs
    // -------------------------------------------------------------------------

    public function monitoringIndex(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);
        $shift = $this->normalizeShift($request->input('shift'));
        $scheduledJobs   = $this->monitoringScheduledQuery($request, $site, $shift)->get();
        $unscheduledJobs = $this->monitoringUnscheduledQuery($request, $site, $shift)->get();

        // Export tersedia hanya jika semua job yang tampil sudah di-approve
        $allApproved = $scheduledJobs->every(fn ($j) => $j->is_approved)
            && $unscheduledJobs->every(fn ($j) => $j->is_approved)
            && ($scheduledJobs->count() + $unscheduledJobs->count()) > 0;

        return response()->json([
            'data' => [
                'scheduledJobs'   => $scheduledJobs,
                'unscheduledJobs' => $unscheduledJobs,
            ],
            'meta' => [
                'site'          => $site,
                'all_approved'  => $allApproved,
                'users'         => User::query()->select('id', 'name')->orderBy('name')->get(),
                'shift_options' => $this->shiftOptions(),
                'filters'       => array_merge(
                    $request->only(['start_date', 'end_date', 'status']),
                    ['shift' => $shift]
                ),
                'can_approve' => in_array($request->user()->role, self::ROLE_APPROVE_JOB, true),
            ],
        ]);
    }

    public function monitoringExport(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);
        $shift = $this->normalizeShift($request->input('shift'));

        $validated = $request->validate([
            'format'     => ['nullable', 'string', 'in:csv,xlsx'],
            'scope'      => ['nullable', 'string', 'in:scheduled,unscheduled,all'],
            'start_date' => ['nullable', 'date'],
            'end_date'   => ['nullable', 'date'],
            'status'     => ['nullable', 'string'],
        ]);

        // Pastikan semua job dalam filter sudah di-approve sebelum export
        $scheduledCount   = $this->monitoringScheduledQuery($request, $site, $shift)->where('is_approved', false)->count();
        $unscheduledCount = $this->monitoringUnscheduledQuery($request, $site, $shift)->where('is_approved', false)->count();

        if ($scheduledCount + $unscheduledCount > 0) {
            abort(403, 'Export hanya tersedia setelah semua job di-approve oleh Group Leader.');
        }

        $format = strtolower((string) ($validated['format'] ?? 'xlsx'));
        $scope  = strtolower((string) ($validated['scope'] ?? 'scheduled'));

        $rows = collect();

        if (in_array($scope, ['scheduled', 'all'], true)) {
            $rows = $rows->concat(
                $this->mapJobsForExport(
                    $this->monitoringScheduledQuery($request, $site, $shift)->get(),
                    'scheduled'
                )
            );
        }

        if (in_array($scope, ['unscheduled', 'all'], true)) {
            $rows = $rows->concat(
                $this->mapJobsForExport(
                    $this->monitoringUnscheduledQuery($request, $site, $shift)->get(),
                    'unscheduled'
                )
            );
        }

        $filename = sprintf('monitoring-jobs-%s-%s.%s', strtolower($site), $scope, $format);

        return Excel::download(
            new MonitoringJobsExport($rows->values()),
            $filename,
            $format === 'csv' ? ExcelWriter::CSV : ExcelWriter::XLSX
        );
    }

    // -------------------------------------------------------------------------
    // Job Unschedule (semua role bisa CRUD)
    // -------------------------------------------------------------------------

    public function unscheduleIndex(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);
        $shift = $this->normalizeShift($request->input('shift'));

        $today = Carbon::today();
        $user  = $request->user();

        $query = DailyJob::with('creator')
            ->where('category_job', 'unschedule')
            ->when($request->filled(['start_date', 'end_date']), function ($builder) use ($request) {
                $builder->whereBetween('date', [$request->string('start_date'), $request->string('end_date')]);
            }, function ($builder) use ($today) {
                $builder->whereDate('date', $today);
            })
            ->when($shift !== null, fn ($builder) => $builder->where('shift', $shift))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->when(
                ! in_array($user->role, ['ict_developer', 'ict_group_leader', 'ict_ho'], true),
                fn ($builder) => $builder->whereJsonContains('crew', $user->id)
            )
            ->where('site', $site)
            ->orderByDesc('created_at');

        return response()->json([
            'data' => $query->paginate((int) $request->integer('per_page', 25)),
            'meta' => [
                'site'          => $site,
                'users'         => User::query()->select('id', 'name')->orderBy('name')->get(),
                'shift_options' => $this->shiftOptions(),
                'filters'       => array_merge(
                    $request->only(['start_date', 'end_date', 'status']),
                    ['shift' => $shift]
                ),
            ],
        ]);
    }

    public function unscheduleMeta(Request $request)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);

        return response()->json([
            'site'          => $site,
            'users'         => $this->siteCrewOptions($site),
            'shift_options' => $this->shiftOptions(),
            'categories'    => RootCauseCategories::where('site_type', 'SITE')->pluck('category_root_cause'),
        ]);
    }

    public function unscheduleProblems(Request $request)
    {
        $request->validate(['category' => ['required', 'string']]);

        return response()->json([
            'data' => RootCauseProblem::where('kategori_name', $request->string('category'))
                ->pluck('root_cause_problem'),
        ]);
    }

    public function unscheduleStore(Request $request)
    {
        $site = $this->resolveSite($request);
        // Semua role bisa buat unschedule job
        $this->authorizeSiteAccess($request, $site);

        $validated = $request->validate([
            'shared.shift'            => ['nullable', 'string', 'in:SHIFT_1,SHIFT_2,pagi,malam'],
            'shared.crew'             => ['nullable', 'array'],
            'shared.crew.*'           => ['nullable'],
            'jobs'                    => ['required', 'array', 'min:1'],
            'jobs.*.code'             => ['nullable', 'string'],
            'jobs.*.job'              => ['required', 'string'],
            'jobs.*.issue'            => ['nullable', 'string'],
            'jobs.*.action_taken'     => ['nullable', 'string'],
            'jobs.*.date'             => ['nullable', 'date'],
            'jobs.*.status'           => ['nullable', 'string', 'in:open,continue,closed'],
            'jobs.*.remark'           => ['nullable', 'string'],
            'jobs.*.start_progress'   => ['nullable', 'date'],
            'jobs.*.end_progress'     => ['nullable', 'date'],
            'jobs.*.category'         => ['required', 'string'],
            'jobs.*.root_cause_problem' => ['nullable', 'string'],
        ]);

        foreach ($validated['jobs'] as $job) {
            DailyJob::create([
                'code'          => $job['code'] ?? null,
                'category_job'  => 'unschedule',
                'description'   => $job['job'],
                'issue'         => $job['issue'] ?? null,
                'action_taken'  => $job['action_taken'] ?? null,
                'date'          => $job['date'] ?? now()->toDateString(),
                'due_date'      => null,
                'status'        => $job['status'] ?? 'open',
                'remark'        => $job['remark'] ?? null,
                'shift'         => $this->normalizeShift(data_get($validated, 'shared.shift', 'SHIFT_1')),
                'crew'          => data_get($validated, 'shared.crew', []),
                'sarana'        => null,
                'start_progress' => $job['start_progress'] ?? null,
                'end_progress'  => $job['end_progress'] ?? null,
                'site'          => $site,
                'category'      => $job['category'],
                'root_cause'    => $job['root_cause_problem'] ?? null,
                'created_by'    => $request->user()->id,
            ]);
        }

        return response()->json(['message' => 'Unscheduled jobs created successfully.'], 201);
    }

    public function unscheduleShow(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeRead($request, $site);

        $job = DailyJob::with(['creator', 'updater'])
            ->where('site', $site)
            ->where('code', $code)
            ->where('category_job', 'unschedule')
            ->firstOrFail();

        return response()->json(['data' => $job]);
    }

    public function unscheduleUpdate(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeSiteAccess($request, $site);

        $job = DailyJob::where('site', $site)
            ->where('code', $code)
            ->where('category_job', 'unschedule')
            ->firstOrFail();

        $validated = $request->validate([
            'description'    => ['required', 'string'],
            'issue'          => ['required', 'string'],
            'action_taken'   => ['required', 'string'],
            'remark'         => ['required', 'string'],
            'status'         => ['required', 'string', 'in:open,continue,closed'],
            'crew'           => ['array'],
            'shift'          => ['required', 'string', 'in:SHIFT_1,SHIFT_2,pagi,malam'],
            'start_progress' => ['nullable', 'date'],
            'end_progress'   => ['nullable', 'date'],
            'category'       => ['required', 'string'],
            'root_cause'     => ['nullable', 'string'],
        ]);

        $validated['shift'] = $this->normalizeShift($validated['shift']);
        $validated['updated_by'] = $request->user()->id;
        $job->update($validated);

        return response()->json(['message' => 'Unscheduled job updated successfully.', 'data' => $job->fresh()]);
    }

    public function unscheduleDestroy(Request $request, string $code)
    {
        $site = $this->resolveSite($request);
        $this->authorizeSiteAccess($request, $site);

        $job = DailyJob::where('site', $site)
            ->where('code', $code)
            ->where('category_job', 'unschedule')
            ->firstOrFail();

        $job->delete();

        return response()->json(['message' => 'Unscheduled job deleted successfully.']);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function resolveSite(Request $request): string
    {
        if (! SiteContext::canAccessAnySite($request)) {
            return strtoupper((string) ($request->user()?->site ?? 'HO'));
        }

        return strtoupper((string) $request->input('site', $request->user()?->site ?? 'HO'));
    }

    private function authorizeRead(Request $request, string $site): void
    {
        $user = $request->user();

        if (! SiteContext::canAccessAnySite($request) && $site !== $user->site) {
            abort(403, 'You dont have permission to access this page.');
        }
    }

    // Hanya cek role, tanpa cek site
    private function authorizeRole(Request $request, array $roles): void
    {
        if (! in_array($request->user()?->role, $roles, true)) {
            abort(403, 'Role kamu tidak memiliki akses untuk aksi ini.');
        }
    }

    // Hanya cek kesesuaian site
    private function authorizeSiteAccess(Request $request, string $site): void
    {
        $user = $request->user();

        if (! SiteContext::canAccessAnySite($request) && $site !== $user->site) {
            abort(403, 'You dont have permission to access this page.');
        }
    }

    private function monitoringScheduledQuery(Request $request, string $site, ?string $shift)
    {
        $today = Carbon::today();

        return DailyJob::with('creator')
            ->when($request->filled(['start_date', 'end_date']), function ($builder) use ($request) {
                $builder->whereBetween('updated_at', [$request->string('start_date'), $request->string('end_date')]);
            }, function ($builder) use ($today) {
                $builder->whereDate('updated_at', $today)
                    ->where('status', '!=', 'zxc');
            })
            ->when($shift !== null, fn ($builder) => $builder->where('shift', $shift))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->whereIn('category_job', ['assignment', 'support'])
            ->where('site', $site)
            ->orderByDesc('updated_at');
    }

    private function monitoringUnscheduledQuery(Request $request, string $site, ?string $shift)
    {
        $today = Carbon::today();

        return DailyJob::with('creator')
            ->where('category_job', 'unschedule')
            ->when($request->filled(['start_date', 'end_date']), function ($builder) use ($request) {
                $builder->whereBetween('updated_at', [$request->string('start_date'), $request->string('end_date')]);
            }, function ($builder) use ($today) {
                $builder->whereDate('updated_at', $today);
            })
            ->when($shift !== null, fn ($builder) => $builder->where('shift', $shift))
            ->when($request->filled('status'), fn ($builder) => $builder->where('status', $request->string('status')))
            ->where('site', $site)
            ->orderByDesc('updated_at');
    }

    private function mapJobsForExport($jobs, string $jobType)
    {
        $users = User::query()->select('id', 'name')->get()->keyBy('id');

        return $jobs->map(function (DailyJob $job) use ($jobType, $users) {
            $crewIds   = collect($job->crew ?? [])->filter(fn ($id) => $id !== null)->values();
            $crewNames = $crewIds->map(fn ($id) => $users->get($id)?->name ?? (string) $id)->values();

            return [
                'job_type'     => $jobType,
                'code'         => $job->code,
                'site'         => $job->site,
                'category_job' => $job->category_job,
                'description'  => $job->description,
                'category'     => $job->category,
                'shift'        => $job->shift,
                'status'       => $job->status,
                'urgency'      => $job->urgency,
                'date'         => optional($job->date)->format('Y-m-d'),
                'due_date'     => $job->due_date,
                'start_progress' => optional($job->start_progress)->format('Y-m-d H:i:s'),
                'end_progress'   => optional($job->end_progress)->format('Y-m-d H:i:s'),
                'issue'        => $job->issue,
                'root_cause'   => $job->root_cause,
                'action_taken' => $job->action_taken,
                'remark'       => $job->remark,
                'sarana'       => $job->sarana,
                'crew_ids'     => $crewIds->implode(', '),
                'crew_names'   => $crewNames->implode(', '),
                'is_approved'  => $job->is_approved ? 'Ya' : 'Tidak',
                'approved_at'  => optional($job->approved_at)->format('Y-m-d H:i:s'),
                'creator_name' => $job->creator?->name,
                'created_at'   => optional($job->created_at)->format('Y-m-d H:i:s'),
                'updated_at'   => optional($job->updated_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    private function siteCrewOptions(string $site)
    {
        return User::where('site', $site)
            ->where('ict_group', 'Y')
            ->get()
            ->map(fn ($user) => [
                'id'    => $user->id,
                'name'  => $user->name,
                'label' => "{$user->name} - {$user->site} - {$user->nrp}",
            ])
            ->values();
    }

    private function normalizeShift(null|string $shift): ?string
    {
        if ($shift === null || $shift === '') {
            return null;
        }

        return match (strtoupper((string) $shift)) {
            'SHIFT_1', 'PAGI' => 'pagi',
            'SHIFT_2', 'MALAM' => 'malam',
            default => strtolower((string) $shift),
        };
    }

    private function shiftOptions(): array
    {
        return [
            ['label' => 'SHIFT_1', 'value' => 'pagi',  'legacy_value' => 'SHIFT_1'],
            ['label' => 'SHIFT_2', 'value' => 'malam', 'legacy_value' => 'SHIFT_2'],
        ];
    }
}
