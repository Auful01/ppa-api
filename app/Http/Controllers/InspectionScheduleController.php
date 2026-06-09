<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use Barryvdh\DomPDF\Facade\Pdf;

class InspectionScheduleController extends Controller
{

    protected function getSiteFromRequest(Request $request): string
    {
        $prefix = $request->route()->getPrefix();
        return str_replace('inspection-scheduler-laptop-', '', $prefix);
    }

    public function index(Request $request)
    {
        $prefix = $request->route()->getPrefix();

        $site_link = str_replace('inspection-scheduler-all-', '', $prefix);
        $site = strtoupper(str_replace('inspection-scheduler-all-', '', $prefix));

        // dd($site);

        $device = $request->get('device', 'laptop');

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

        switch ($device) {

            case 'computer':
                $Q = match (true) {
                    $month <= 3 => 'Q1',
                    $month <= 6 => 'Q2',
                    $month <= 9 => 'Q3',
                    default => 'Q4',
                };

                $schedules = DB::table('schedule_computer')
                    ->join('inv_computers', 'schedule_computer.id_computer', '=', 'inv_computers.id')
                    ->select(
                        'schedule_computer.id',
                        'schedule_computer.tanggal_inspection',
                        'schedule_computer.actual_inspection',
                        'inv_computers.computer_code AS device_code',
                        'inv_computers.dept',
                        'inv_computers.site'
                    )
                    ->where('schedule_computer.site', $site)
                    ->where('schedule_computer.quarter', $Q)
                    ->where('schedule_computer.tahun', $year)
                    ->get();


                $summary = collect($schedules)->groupBy(fn($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
                    ->map(function ($group) {
                        return [
                            'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                            'departments' => $group->pluck('dept')->unique()->values(),
                        ];
                    })->values();

                $inspectionStats = DB::table('schedule_computer')
                    ->where('site', $site)
                    ->where('quarter', $Q)
                    ->where('tahun', $year)
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

                $kategori = 'Computer';
                break;

            case 'printer':

                $schedules = DB::table('schedule_printer')
                    ->join('inv_printers', 'schedule_printer.id_printer', '=', 'inv_printers.id')
                    ->select(
                        'schedule_printer.id',
                        'schedule_printer.tanggal_inspection',
                        'schedule_printer.actual_inspection',
                        'inv_printers.printer_code AS device_code',
                        'inv_printers.department AS dept',
                        'inv_printers.site'
                    )
                    ->where('schedule_printer.site', $site)
                    ->where('schedule_printer.bulan', $month)
                    ->where('schedule_printer.tahun', $year)
                    ->get();


                $summary = collect($schedules)->groupBy(fn($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
                    ->map(function ($group) {
                        return [
                            'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                            'departments' => $group->pluck('dept')->unique()->values(),
                        ];
                    })->values();


                $inspectionStats = DB::table('schedule_printer')
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

                $kategori = 'Printer';
                break;

            case 'mobile_tower':

                $schedules = DB::table('schedule_mobile_tower')
                    ->join('inv_mobile_towers', 'schedule_mobile_tower.id_mobile_tower', '=', 'inv_mobile_towers.id')
                    ->select(
                        'schedule_mobile_tower.id',
                        'schedule_mobile_tower.tanggal_inspection',
                        'schedule_mobile_tower.actual_inspection',
                        'inv_mobile_towers.mt_code AS device_code',
                        'inv_mobile_towers.site'
                    )
                    ->where('schedule_mobile_tower.site', $site)
                    ->where('schedule_mobile_tower.bulan', $month)
                    ->where('schedule_mobile_tower.tahun', $year)
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

                $summary = [];

                $kategori = 'Mobile Tower';
                break;

            default: // laptop
                $schedules = DB::table('schedule_laptop')
                    ->join('inv_laptops', 'schedule_laptop.id_laptop', '=', 'inv_laptops.id')
                    ->select(
                        'schedule_laptop.id',
                        'schedule_laptop.tanggal_inspection',
                        'schedule_laptop.actual_inspection',
                        'inv_laptops.laptop_code AS device_code',
                        'inv_laptops.dept',
                        'inv_laptops.site'
                    )
                    ->where('schedule_laptop.site', $site)
                    ->where('schedule_laptop.tahun', $year)
                    ->get();

                $summary = collect($schedules)->groupBy(fn($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
                    ->map(function ($group) {
                        return [
                            'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                            'departments' => $group->pluck('dept')->unique()->values(),
                        ];
                    })->values();

                $inspectionStats = DB::table('schedule_laptop')
                    ->where('site', $site)
                    ->where('tahun', $year)
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

                $kategori = 'Laptop';
        }

        $total = $inspectionStats->total ?: 1; // prevent division by zero

        $sudahSesuai = (int) $inspectionStats->sudahSesuai;
        $belumSesuai = (int) $inspectionStats->belumSesuai;

        $sudahSesuaiPercentage = round(($sudahSesuai / $total) * 100, 2);
        $belumSesuaiPercentage = round(($belumSesuai / $total) * 100, 2);

        // dd($device);

        return Inertia::render('InspectionSchedule/IndexLaptop', [
            'schedules' => $schedules,
            'summary' => $summary,
            'site_link' => $site_link,
            'labelPeriode' => $year,
            'kategori' => $device,
            'sudahSesuai' => [$sudahSesuaiPercentage],
            'belumSesuai' => [$belumSesuaiPercentage],
        ]);
    }

    public function update(Request $request, $id)
    {

        $prefix = $request->route()->getPrefix();

        $site_link = str_replace('inspection-scheduler-all-', '', $prefix);
        $site = strtoupper(str_replace('inspection-scheduler-all-', '', $prefix));

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

        $kategori = $request->kategori;

        if ($kategori === 'laptop') {

            DB::table('schedule_laptop')
                ->where('id', $id)
                ->update([
                    'tanggal_inspection' => $date->toDateString(),
                    'bulan' => $date->format('m'),
                    'tahun' => $date->format('Y'),
                    'updated_at' => now(),
                ]);

        } else if ($kategori === 'computer') {

            DB::table('schedule_computer')
                ->where('id', $id)
                ->update([
                    'tanggal_inspection' => $date->toDateString(),
                    'bulan' => $date->format('m'),
                    'tahun' => $date->format('Y'),
                    'updated_at' => now(),
                ]);

        } else if ($kategori === 'printer') {

            DB::table('schedule_printer')
                ->where('id', $id)
                ->update([
                    'tanggal_inspection' => $date->toDateString(),
                    'bulan' => $date->format('m'),
                    'tahun' => $date->format('Y'),
                    'updated_at' => now(),
                ]);

        } else if ($kategori === 'mobile_tower') {
            DB::table('schedule_mobile_tower')
                ->where('id', $id)
                ->update([
                    'tanggal_inspection' => $date->toDateString(),
                    'bulan' => $date->format('m'),
                    'tahun' => $date->format('Y'),
                    'updated_at' => now(),
                ]);
        }

        return redirect()
            ->route("inspection-scheduler-all.$site_link.index", [
                'device' => $kategori,
            ])
            ->with('success', 'Schedule updated successfully.');

    }

    public function exportPdf(Request $request, string $site)
    {
        // $month = $request->query('month');
        // $dept = $request->query('dept');

        $kategori = $request->query('device');

        $user = Auth::user();
        $auth = $user->role;
        $userSite = $user->site;

        if ($auth != 'ict_developer' && $site != $userSite) {
            abort(403, 'You dont have permission to access this page.');
        }
        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        $thisMonthTeks = Carbon::create()->month((int) $month)->translatedFormat('F');

        // $kategori = $request->kategori;

        $triwulan = match (true) {
            $month >= 1 && $month <= 3 => 'Triwulan 1',
            $month >= 4 && $month <= 6 => 'Triwulan 2',
            $month >= 7 && $month <= 9 => 'Triwulan 3',
            $month >= 10 && $month <= 12 => 'Triwulan 4',
        };

        $periodeLabel = match (true) {
            $month >= 1 && $month <= 3 => 'Januari - Februari - Maret',
            $month >= 4 && $month <= 6 => 'April - Mei - Juni',
            $month >= 7 && $month <= 9 => 'Juli - Agustus - September',
            $month >= 10 && $month <= 12 => 'Oktober - November - Desember',
        };

        $Q = match (true) {
            $month >= 1 && $month <= 3 => 'Q1',
            $month >= 4 && $month <= 6 => 'Q2',
            $month >= 7 && $month <= 9 => 'Q3',
            $month >= 10 && $month <= 12 => 'Q4',
        };

        // dd($kategori);

        if ($kategori === 'laptop') {

            // Build the base query
            $query = DB::table('schedule_laptop')
                ->join('inv_laptops', 'schedule_laptop.id_laptop', '=', 'inv_laptops.id')
                ->select(
                    'schedule_laptop.id',
                    'schedule_laptop.id_laptop',
                    'schedule_laptop.tanggal_inspection',
                    'schedule_laptop.actual_inspection',
                    'schedule_laptop.bulan',
                    'schedule_laptop.tahun',
                    'inv_laptops.laptop_code',
                    'inv_laptops.dept',
                    'inv_laptops.site'
                )
                ->where('schedule_laptop.site', $site)
                ->where('schedule_laptop.tahun', $year);

        } else if ($kategori === 'computer') {

            $query = DB::table('schedule_computer')
                ->join('inv_computers', 'schedule_computer.id_computer', '=', 'inv_computers.id')
                ->select(
                    'schedule_computer.id',
                    'schedule_computer.id_computer',
                    'schedule_computer.tanggal_inspection',
                    'schedule_computer.actual_inspection',
                    'schedule_computer.bulan',
                    'schedule_computer.tahun',
                    'inv_computers.computer_code',
                    'inv_computers.dept',
                    'inv_computers.site'
                )
                ->where('schedule_computer.site', $site)
                ->where('schedule_computer.quarter', $Q)
                ->where('schedule_computer.tahun', $year);

        } else if ($kategori === 'printer') {

            $query = DB::table('schedule_printer')
                ->join('inv_printers', 'schedule_printer.id_printer', '=', 'inv_printers.id')
                ->select(
                    'schedule_printer.id',
                    'schedule_printer.id_printer',
                    'schedule_printer.tanggal_inspection',
                    'schedule_printer.actual_inspection',
                    'schedule_printer.bulan',
                    'schedule_printer.tahun',
                    'inv_printers.printer_code',
                    'inv_printers.department',
                    'inv_printers.site'
                )
                ->where('schedule_printer.site', $site)
                ->where('schedule_printer.bulan', $month)
                ->where('schedule_printer.tahun', $year);

        } else if ($kategori === 'mobile_tower') {

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
        }



        // Optional filters
        // if ($month) {
        //     $monthNumber = Carbon::parse("1 $month")->month;
        //     $query->whereMonth('tanggal_inspection', $monthNumber);
        // }

        // if ($dept) {
        //     $query->where('inv_laptops.dept', $dept);
        // }

        $schedules = $query->orderBy('tanggal_inspection')->get();

        if ($kategori === 'laptop') {

            $pdf = Pdf::loadView('itportal.scheduleInspeksi.inspection-schedule-laptop', [
                'schedules' => $schedules,
                // 'month' => $month,
                // 'dept' => $dept,
                'site' => $site,
                'periodeLabel' => $year,
            ])->setPaper('A4', 'portrait');

            return $pdf->stream("jadwal-inspeksi-laptop-$year-$site.pdf");

        } else if ($kategori === 'computer') {

            $pdf = Pdf::loadView('itportal.scheduleInspeksi.inspection-schedule-komputer', [
                'schedules' => $schedules,
                // 'month' => $month,
                // 'dept' => $dept,
                'site' => $site,
                'periodeLabel' => $periodeLabel,
                'triwulan' => $triwulan,
            ])->setPaper('A4', 'portrait');

            return $pdf->stream("jadwal-inspeksi-komputer-$triwulan-$site.pdf");

        } else if ($kategori === 'printer') {

            $pdf = Pdf::loadView('itportal.scheduleInspeksi.inspection-schedule-printer', [
                'schedules' => $schedules,
                // 'thisMonthTeks' => $thisMonthTeks,
                // 'dept' => $dept,
                'site' => $site,
                'periodeLabel' => $thisMonthTeks . ' ' . $year,
            ])->setPaper('A4', 'portrait');

            return $pdf->stream("jadwal-inspeksi-printer-$month-$year-$site.pdf");

        } else if ($kategori === 'mobile_tower') {

            $pdf = Pdf::loadView('itportal.scheduleInspeksi.inspection-schedule-mobileTower', [
                'schedules' => $schedules,
                // 'month' => $month,
                // 'dept' => $dept,
                'site' => $site,
                'periodeLabel' => $thisMonthTeks . ' ' . $year,
            ])->setPaper('A4', 'portrait');

            return $pdf->stream("jadwal-inspeksi-mobile_tower-$month-$year-$site.pdf");
        }

    }

    public function generate(Request $request)
    {

        $path = $request->path();
        $site_link = str_replace('generate-inspection-scheduler-laptop-', '', $path);
        $site = strtoupper($site_link);

        $unscheduledLaptops = DB::table('inv_laptops')
            ->leftJoin('schedule_laptop', 'inv_laptops.id', '=', 'schedule_laptop.id_laptop')
            ->whereNull('schedule_laptop.id_laptop')
            ->where('inv_laptops.site', $site)
            // ->where('inv_laptops.site', 'bib')
            ->select('inv_laptops.id', 'inv_laptops.laptop_code', 'inv_laptops.dept', 'inv_laptops.site')
            ->get();

        if ($unscheduledLaptops->isEmpty()) {
            return response()->json(['message' => 'All laptops are already scheduled.'], 200);
        }

        // Group and sort departments
        $groupedByDept = $unscheduledLaptops->groupBy('dept');
        $sortedDepts = $groupedByDept->sortByDesc(fn($group) => $group->count());

        // Get department names
        $departments = $sortedDepts->keys()->values();
        $deptCount = $departments->count();

        if ($deptCount > 12) {
            return response()->json(['error' => 'Cannot assign more than 12 departments in one year without overlap.'], 400);
        }

        // Evenly assign each department a month
        $deptDateLimits = [];

        if ($deptCount === 1) {
            $assignedMonths = [6]; // Only one dept: assign to June
        } else {
            $assignedMonths = [];
            for ($i = 0; $i < $deptCount; $i++) {
                $month = round(1 + ($i * (11 / ($deptCount - 1)))); // Evenly spread between Jan (1) and Dec (12)
                $assignedMonths[] = (int) $month;
            }
            $assignedMonths = array_unique($assignedMonths);

            if (count($assignedMonths) < $deptCount) {
                return response()->json(['error' => 'Unable to assign unique months for departments.'], 500);
            }
        }

        foreach ($departments as $index => $deptName) {
            $month = $assignedMonths[$index];
            $start = Carbon::create(2025, $month, 1);
            $end = $start->copy()->endOfMonth();

            $deptDateLimits[$deptName] = [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ];
        }

        // Build the schedule
        $schedule = [];

        foreach ($sortedDepts as $dept => $laptops) {
            $range = $deptDateLimits[$dept];
            $deptStart = Carbon::parse($range['start']);
            $deptEnd = Carbon::parse($range['end']);
            $deptDays = $deptStart->diffInDays($deptEnd) + 1;

            // Generate dates for that department's month
            $dates = collect();
            for ($i = 0; $i < $deptDays; $i++) {
                $dates->push($deptStart->copy()->addDays($i)->toDateString());
            }

            // Take ~70% of the month randomly
            $dates = $dates->shuffle()->take((int) round($deptDays * 0.7))->values();

            // Assign 2–3 laptops per date
            $repeatDates = [];
            $laptopsRemaining = $laptops->count();
            $dateIndex = 0;

            while ($laptopsRemaining > 0 && $dateIndex < count($dates)) {
                $batchSize = rand(2, 3);
                for ($i = 0; $i < $batchSize && $laptopsRemaining > 0; $i++) {
                    $repeatDates[] = $dates[$dateIndex];
                    $laptopsRemaining--;
                }
                $dateIndex++;
            }

            // If still laptops left, reuse available dates
            $dateIndex = 0;
            while ($laptopsRemaining > 0) {
                $repeatDates[] = $dates[$dateIndex % count($dates)];
                $laptopsRemaining--;
                $dateIndex++;
            }

            sort($repeatDates);

            // Assign dates to laptops
            $laptopIndex = 0;
            $sortedLaptops = $laptops->sortBy('laptop_code')->values();

            foreach ($sortedLaptops as $laptop) {
                $inspectionDate = Carbon::parse($repeatDates[$laptopIndex]);

                $schedule[] = [
                    'id_laptop' => $laptop->id,
                    'tanggal_inspection' => $inspectionDate->toDateString(),
                    'bulan' => $inspectionDate->format('m'),
                    'tahun' => $inspectionDate->format('Y'),
                    'laptop_code' => $laptop->laptop_code,
                    'dept' => $laptop->dept,
                    'site' => $laptop->site,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $laptopIndex++;
            }
        }

        DB::table('schedule_laptop')->insert($schedule);

        return response()->json([
            'message' => 'Schedule generated successfully.',
            'total_scheduled' => count($schedule),
        ], 201);
    }
}
