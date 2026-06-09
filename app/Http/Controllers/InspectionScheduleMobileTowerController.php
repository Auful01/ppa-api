<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use Barryvdh\DomPDF\Facade\Pdf;

class InspectionScheduleMobileTowerController extends Controller
{

    protected function getSiteFromRequest(Request $request): string
    {
        $prefix = $request->route()->getPrefix();
        return str_replace('inspection-scheduler-mobileTower-', '', $prefix);
    }

    public function index(Request $request)
    {
        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;

        if ($auth == 'ict_technician' || $auth == 'ict_group_leader' || $auth == 'ict_admin') {
            if ($site != $userSite) {
                abort(403, 'You dont have permission to access this page.');
            }
        }

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $thisMonthTeks = Carbon::create()->month((int) $month)->translatedFormat('F');

        $schedules = DB::table('schedule_mobile_tower')
            ->join('inv_mobile_towers', 'schedule_mobile_tower.id_mobile_tower', '=', 'inv_mobile_towers.id')
            ->select(
                'schedule_mobile_tower.id',
                'schedule_mobile_tower.id_mobile_tower',
                'schedule_mobile_tower.tanggal_inspection',
                'schedule_mobile_tower.actual_inspection',
                'schedule_mobile_tower.bulan',
                'schedule_mobile_tower.tahun',
                'inv_mobile_towers.mt_code',
                'inv_mobile_towers.site'
            )
            ->where('schedule_mobile_tower.site', $site)
            ->where('schedule_mobile_tower.bulan', $month)
            ->where('schedule_mobile_tower.tahun', $year)
            ->orderBy('tanggal_inspection')
            ->get();


        $inspectionStats = DB::table('schedule_mobile_tower')
            ->where('site', $site)
            ->where('tahun', $year)
            ->where('bulan', $month)
            ->whereNotNull('actual_inspection')
            ->selectRaw("
                SUM(CASE 
                    WHEN DATE(actual_inspection) = DATE(tanggal_inspection) 
                    THEN 1 ELSE 0 END) AS sudahSesuai,
                SUM(CASE 
                    WHEN DATE(actual_inspection) != DATE(tanggal_inspection) 
                    THEN 1 ELSE 0 END) AS belumSesuai,
                COUNT(*) AS total
            ")
            ->first();

        $total = $inspectionStats->total ?: 1; // prevent division by zero

        $sudahSesuai = (int) $inspectionStats->sudahSesuai;
        $belumSesuai = (int) $inspectionStats->belumSesuai;

        $sudahSesuaiPercentage = round(($sudahSesuai / $total) * 100, 2);
        $belumSesuaiPercentage = round(($belumSesuai / $total) * 100, 2);

        return Inertia::render('InspectionSchedule/IndexMobileTower', [
            'schedules' => $schedules,
            'site_link' => $site_link,
            'thisMonthTeks' => $thisMonthTeks . ' ' . $year,
            'sudahSesuai' => [$sudahSesuaiPercentage],
            'belumSesuai' => [$belumSesuaiPercentage],
        ]);
    }

    public function update(Request $request, $id)
    {

        $site_link = $this->getSiteFromRequest($request);
        $site = strtoupper($this->getSiteFromRequest($request));

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }

        $request->validate([
            'tanggal_inspection' => 'required|date',
        ]);

        $date = Carbon::parse($request->tanggal_inspection);

        DB::table('schedule_mobile_tower')
            ->where('id', $id)
            ->update([
                'tanggal_inspection' => $date->toDateString(),
                'bulan' => $date->format('m'),
                'tahun' => $date->format('Y'),
                'updated_at' => now(),
            ]);

        return redirect()->route("inspection-scheduler-mobileTower.$site_link.index")->with('success', 'Schedule updated successfully.');
    }

    public function exportPdf(Request $request, string $site)
    {
        // $month = $request->query('month');
        // $dept = $request->query('dept');

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $thisMonthTeks = Carbon::create()->month((int) $month)->translatedFormat('F');

        // Build the base query
        $query = DB::table('schedule_mobile_tower')
            ->join('inv_mobile_towers', 'schedule_mobile_tower.id_mobile_tower', '=', 'inv_mobile_towers.id')
            ->select(
                'schedule_mobile_tower.id',
                'schedule_mobile_tower.id_mobile_tower',
                'schedule_mobile_tower.tanggal_inspection',
                'schedule_mobile_tower.actual_inspection',
                'schedule_mobile_tower.bulan',
                'schedule_mobile_tower.tahun',
                'inv_mobile_towers.mt_code',
                'inv_mobile_towers.site'
            )
            ->where('schedule_mobile_tower.site', $site)
            ->where('schedule_mobile_tower.bulan', $month)
            ->where('schedule_mobile_tower.tahun', $year);

        // Optional filters
        // if ($month) {
        //     $monthNumber = Carbon::parse("1 $month")->month;
        //     $query->whereMonth('tanggal_inspection', $monthNumber);
        // }

        // if ($dept) {
        //     $query->where('inv_mobile_towers.dept', $dept);
        // }

        $schedules = $query->orderBy('tanggal_inspection')->get();


        $pdf = Pdf::loadView('itportal.scheduleInspeksi.inspection-schedule-mobileTower', [
            'schedules' => $schedules,
            // 'month' => $month,
            // 'dept' => $dept,
            'site' => $site,
            'periodeLabel' => $thisMonthTeks . ' ' . $year,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("jadwal-inspeksi-mobile_tower-$year-$site.pdf");
    }

    public function generate(Request $request)
    {
        $path = $request->path();
        $site_link = str_replace('generate-inspection-scheduler-mobile-tower-', '', $path);
        $site = strtoupper($site_link);

        $year = now()->year;
        $month = now()->month;

        /** -----------------------------------------
         *  FETCH UNSCHEDULED MOBILE TOWERS
         *  ----------------------------------------- */
        $unscheduledMobileTowers = DB::table('inv_mobile_towers')
            ->leftJoin('schedule_mobile_tower', function ($join) use ($month, $year) {
                $join->on('inv_mobile_towers.id', '=', 'schedule_mobile_tower.id_mobile_tower')
                    ->where('schedule_mobile_tower.bulan', $month)
                    ->where('schedule_mobile_tower.tahun', $year);
            })
            ->whereNull('schedule_mobile_tower.id_mobile_tower')
            ->where('inv_mobile_towers.site', $site)
            ->select('inv_mobile_towers.id', 'inv_mobile_towers.mt_code', 'inv_mobile_towers.site')
            ->orderBy('inv_mobile_towers.mt_code')
            ->get();

        if ($unscheduledMobileTowers->isEmpty()) {
            return response()->json([
                'message' => 'All mobile towers already scheduled this month'
            ], 200);
        }

        /** -----------------------------------------
         *  DYNAMIC DAILY CAPACITY
         *  ----------------------------------------- */
        $totalUnits = $unscheduledMobileTowers->count();
        $maxDays = 15;
        $maxPerDay = (int) ceil($totalUnits / $maxDays);

        /** -----------------------------------------
         *  PREPARE DAYS 1–15
         *  ----------------------------------------- */
        $days = [];
        for ($d = 1; $d <= 15; $d++) {
            $date = Carbon::create($year, $month, $d)->toDateString();
            $days[$date] = [
                'count' => 0,
            ];
        }

        $schedule = [];

        /** -----------------------------------------
         *  SCHEDULING ENGINE (ROUND-ROBIN)
         *  ----------------------------------------- */
        $dayKeys = array_keys($days);
        $dayIndex = 0;

        foreach ($unscheduledMobileTowers as $mobileTower) {

            // Find next day with available capacity
            while ($days[$dayKeys[$dayIndex]]['count'] >= $maxPerDay) {
                $dayIndex = ($dayIndex + 1) % count($dayKeys);
            }

            $date = $dayKeys[$dayIndex];

            $schedule[] = [
                'id_mobile_tower' => $mobileTower->id,
                'tanggal_inspection' => $date,
                'bulan' => $month,
                'tahun' => $year,
                'mobile_tower_code' => $mobileTower->mt_code,
                'dept' => '-',
                'site' => $mobileTower->site,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $days[$date]['count']++;

            // Move pointer forward
            $dayIndex = ($dayIndex + 1) % count($dayKeys);
        }

        /** -----------------------------------------
         *  SAFETY CHECK
         *  ----------------------------------------- */
        if (count($schedule) !== $totalUnits) {
            return response()->json([
                'error' => 'Not all mobile towers could be scheduled',
                'scheduled' => count($schedule),
                'total' => $totalUnits,
            ], 422);
        }

        DB::table('schedule_mobile_tower')->insert($schedule);

        return response()->json([
            'message' => 'Monthly mobile tower schedule generated successfully',
            'month' => $month,
            'year' => $year,
            'total_scheduled' => count($schedule),
        ], 201);
    }


}
