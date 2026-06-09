<?php

namespace App\Http\Controllers;

use App\Models\DailyJob;
use App\Models\RootCauseCategories;
use App\Models\RootCauseProblem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class UnscheduleJobController extends Controller
{
    protected function getSiteFromRequest(Request $request): string
    {
        $prefix = $request->route()->getPrefix();
        return str_replace('unschedule-jobs-', '', $prefix);
    }

    public function getCategories()
    {
        dd(RootCauseCategories::where('site_type', 'SITE')->pluck('category_root_cause'));
        return RootCauseCategories::where('site_type', 'SITE')->pluck('category_root_cause');
    }

    public function getProblems(Request $request)
    {
        dd(RootCauseProblem::where('kategori_name', $request->category)->pluck('root_cause_problem'));
        return RootCauseProblem::where('kategori_name', $request->category)->pluck('root_cause_problem');
    }

    public function index(Request $request)
    {
        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $today = Carbon::today();
        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;

        if ($auth == 'ict_technician' || $auth == 'ict_group_leader' || $auth == 'ict_admin') {
            if ($site != $userSite) {
                abort(403, 'You dont have permission to access this page.');
            }
        }

        // Optional filters
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $shift = $request->shift;
        $status = $request->status;

        $jobs = DailyJob::with('creator')
            ->where('category_job', 'unschedule')
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }, function ($query) use ($today) {
                // default filter if no date range is selected
                $query->where(function ($q) use ($today) {
                    $q->whereDate('date', $today);
                });
            })
            ->when($shift, function ($query) use ($shift) {
                $query->where('shift', $shift);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($auth !== 'ict_developer' && $auth !== 'ict_group_leader' && $auth !== 'ict_ho', function ($query) use ($user) {
                $query->whereJsonContains('crew', $user->id);
            })
            ->when($site, function ($query) use ($site) {
                $query->where('site', $site);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $users = User::select('id', 'name')->get();
        // $is_admin = $auth == 'ict_developer' || $auth == 'ict_group_leader'; //tidak perlu di unschedule job

        return Inertia::render('UnscheduleJobs/JobListView', [
            'jobs' => $jobs,
            // 'canCreate' => $is_admin,
            'role' => $auth,
            'users' => $users,
            'site_link' => $site_link,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'shift' => $shift,
                'status' => $status,
            ],
        ]);
    }

    public function create(Request $request)
    {
        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;
        // dd($auth);

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }

        $users = User::where('site', $site)->where('ict_group', 'Y')
            ->where('ict_group', 'Y')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'label' => "{$user->name} - {$user->site} - {$user->nrp}",
                    // Include other needed fields
                ];
            });

        $categories = RootCauseCategories::where('site_type', 'SITE')
            ->pluck('category_root_cause');

        // dd($categories);

        return Inertia::render('UnscheduleJobs/CreateJobAssign', [
            'users' => $users,
            'site_link' => $site_link,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {

        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;
        // dd($auth);

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }

        $data = $request->all();

        // dd($request->all());

        $user = Auth::user();

        foreach ($data['jobs'] as $job) {
            DailyJob::create([
                'code' => $job['code'] ?? null,
                'category_job' => 'unschedule',
                'description' => $job['job'] ?? null,
                'issue' => $job['issue'] ?? null,
                'action_taken' => $job['action_taken'] ?? null,
                'date' => $job['date'] ?? null,
                'due_date' => null,
                'status' => $job['status'] ?? 'open',
                'remark' => $job['remark'] ?? null,
                'shift' => $data['shared']['shift'] ?? 'SHIFT_1',
                'crew' => isset($data['shared']['crew']) ? $data['shared']['crew'] : [],
                'sarana' => null,
                'start_progress' => $job['start_progress'] ?? null,
                'end_progress' => $job['end_progress'] ?? null,
                'site' => $site, // Replace or set dynamically
                'category' => $job['category'], // Replace or set dynamically
                'root_cause' => $job['root_cause_problem'] ?? null, // Replace or set dynamically
                'created_by' => $user->id,
            ]);
        }

        return redirect()->route("unschedule-jobs.$site_link.index")
            ->with('success', 'Jobs created successfully.');
    }

    public function edit(string $code, Request $request)
    {
        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }

        $dailyJob = DailyJob::where('code', $code)->firstOrFail();
        // $users = User::select('id', 'name')->get();
        $users = User::where('site', $site)->where('ict_group', 'Y')
            ->where('ict_group', 'Y')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'label' => "{$user->name} - {$user->site} - {$user->nrp}",
                    // Include other needed fields
                ];
            });

        $categories = RootCauseCategories::where('site_type', 'SITE')
            ->pluck('category_root_cause');

        return Inertia::render('UnscheduleJobs/EditJobAssign', [
            'job' => $dailyJob,
            'users' => $users,
            'site_link' => $site_link,
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, string $code)
    {

        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;
        // dd($auth);

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }

        $dailyJob = DailyJob::where('code', $code)->firstOrFail();

        $data = $request->validate([
            'description' => 'required|string',
            'issue' => 'required|string',
            'action_taken' => 'required|string',
            'remark' => 'required|string',
            'status' => 'required|string|in:open,continue,closed',
            'crew' => 'array',
            'shift' => 'required|string|in:SHIFT_1,SHIFT_2',
            'start_progress' => 'nullable|date',
            'end_progress' => 'nullable|date',
            'category' => 'required|string',
            'root_cause' => 'nullable|string',
        ]);

        $dailyJob->update($data);

        return redirect()->route("unschedule-jobs.$site_link.index")
            ->with('success', 'Job updated successfully.');
    }

    public function destroy($code, Request $request, )
    {

        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;
        // dd($auth);

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }

        $job = DailyJob::where('code', $code)->firstOrFail();
        $job->delete();

        return redirect()->route("unschedule-jobs.$site_link.index")
            ->with('successDelete', 'Job deleted successfully.');
    }
}
