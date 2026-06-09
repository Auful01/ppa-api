<?php

namespace App\Http\Controllers;

use App\Models\DailyJob;
use App\Models\RootCauseCategories;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class DailyJobController extends Controller
{

    protected function getSiteFromRequest(Request $request): string
    {
        $prefix = $request->route()->getPrefix();
        return str_replace('daily-jobs-', '', $prefix);
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
            ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            }, function ($query) use ($today) {
                // default filter if no date range is selected
                $query->where(function ($q) use ($today) {
                    $q->where(function ($q2) use ($today) {
                        $q2->whereDate('date', $today);
                    })->orWhere(function ($q3) use ($today) {
                        $q3->where('status', '!=', 'closed')
                            ->whereDate('date', '!=', $today);
                    });
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
            ->where('category_job', '!=', 'unschedule')
            ->when($site, function ($query) use ($site) {
                $query->where('site', $site);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $users = User::select('id', 'name')->get();
        $is_admin = $auth == 'ict_developer' || $auth == 'ict_group_leader' || $auth == 'ict_ho';


        return Inertia::render('DailyJobs/JobListView', [
            'jobs' => $jobs,
            'canCreate' => $is_admin,
            'users' => $users,
            'site_link' => $site_link,
            'role' => $auth,
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

        // dd($site);

        $users = User::where('site', $site)->where('ict_group', 'Y')
            ->where('ict_group', 'Y')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'label' => "{$user->name} - {$user->site} - {$user->nrp}",
                    // Include other needed fields
                ];
            });

        return Inertia::render('DailyJobs/CreateJobAssign', [
            'users' => $users,
            'site_link' => $site_link,
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
                'category_job' => $job['categoryJob'] ?? 'assignment',
                'description' => $job['job'] ?? null,
                'date' => $job['date'] ?? null,
                'due_date' => $job['due_date'] ?? null,
                'status' => $job['status'] ?? 'open',
                'remark' => $job['remark'] ?? null,
                'shift' => $data['shared']['shift'] ?? 'SHIFT_1',
                'crew' => isset($data['shared']['crew']) ? $data['shared']['crew'] : [],
                'sarana' => $data['shared']['sarana'] ?? null,
                'site' => $site, // Replace or set dynamically
                'category' => '-', // Replace or set dynamically
                'created_by' => $user->id,
            ]);
        }

        return redirect()->route("daily-jobs.$site_link.index")
            ->with('success', 'Job created successfully!');
    }

    public function edit(string $code, Request $request)
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

        $users = User::where('site', $site)->where('ict_group', 'Y')
            ->where('ict_group', 'Y')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'label' => "{$user->name} - {$user->site} - {$user->nrp}",
                    // Include other needed fields
                ];
            });

        $is_admin = $auth == 'ict_developer' || $auth == 'ict_group_leader' || $auth == 'ict_ho';

        $categories = RootCauseCategories::where('site_type', 'SITE')
            ->pluck('category_root_cause');

        return Inertia::render('DailyJobs/EditJobAssign', [
            'job' => $dailyJob,
            'users' => $users,
            'site_link' => $site_link,
            'canCreate' => $is_admin,
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
            'remark' => 'required|string',
            'due_date' => 'required|date',
            'status' => 'required|string|in:open,continue,closed',
            'category_job' => 'required|string|in:assignment,support',
            'crew' => 'array',
            'sarana' => 'required|string',
            'shift' => 'required|string|in:SHIFT_1,SHIFT_2',
            'action_taken' => 'nullable|string',
            'start_progress' => 'nullable|date',
            'end_progress' => 'nullable|date',
            'category' => 'nullable|string',
            'root_cause' => 'nullable|string',
        ]);

        $dailyJob->update($data);

        return redirect()->route("daily-jobs.$site_link.index")
            ->with('success', 'Job updated successfully!');
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

        return redirect()->route("daily-jobs.$site_link.index")
            ->with('success', 'Job deleted successfully!');
    }
}
