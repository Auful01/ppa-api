<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use Barryvdh\DomPDF\Facade\Pdf;

class InspectionSchedulePrinterController extends Controller
{

    protected function getSiteFromRequest(Request $request): string
    {
        $prefix = $request->route()->getPrefix();
        return str_replace('inspection-scheduler-printer-', '', $prefix);
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

        $schedules = DB::table('schedule_printer')
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
            ->where('schedule_printer.tahun', $year)
            ->orderBy('tanggal_inspection')
            ->get();

        $summary = collect($schedules)->groupBy(fn($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
            ->map(function ($group) {
                return [
                    'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                    'departments' => $group->pluck('department')->unique()->values(),
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

        $total = $inspectionStats->total ?: 1; // prevent division by zero

        $sudahSesuai = (int) $inspectionStats->sudahSesuai;
        $belumSesuai = (int) $inspectionStats->belumSesuai;

        $sudahSesuaiPercentage = round(($sudahSesuai / $total) * 100, 2);
        $belumSesuaiPercentage = round(($belumSesuai / $total) * 100, 2);

        return Inertia::render('InspectionSchedule/IndexPrinter', [
            'schedules' => $schedules,
            'summary' => $summary,
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

        DB::table('schedule_printer')
            ->where('id', $id)
            ->update([
                'tanggal_inspection' => $date->toDateString(),
                'bulan' => $date->format('m'),
                'tahun' => $date->format('Y'),
                'updated_at' => now(),
            ]);

        return redirect()->route("inspection-scheduler-printer.$site_link.index")->with('success', 'Schedule updated successfully.');
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

        // Optional filters
        // if ($month) {
        //     $monthNumber = Carbon::parse("1 $month")->month;
        //     $query->whereMonth('tanggal_inspection', $monthNumber);
        // }

        // if ($dept) {
        //     $query->where('inv_printers.department', $dept);
        // }

        $schedules = $query->orderBy('tanggal_inspection')->get();


        $pdf = Pdf::loadView('itportal.scheduleInspeksi.inspection-schedule-printer', [
            'schedules' => $schedules,
            // 'thisMonthTeks' => $thisMonthTeks,
            // 'dept' => $dept,
            'site' => $site,
            'periodeLabel' => $thisMonthTeks . ' ' . $year,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream("jadwal-inspeksi-printer-$month-$year-$site.pdf");
    }

    public function generate(Request $request)
    {
        $path = $request->path();
        $site_link = str_replace('generate-inspection-scheduler-printer-', '', $path);
        $site = strtoupper($site_link);

        $year = now()->year;
        $month = now()->month;

        // Fetch unscheduled printers
        $unscheduledPrinters = DB::table('inv_printers')
            ->leftJoin('schedule_printer', function ($join) use ($month, $year) {
                $join->on('inv_printers.id', '=', 'schedule_printer.id_printer')
                    ->where('schedule_printer.bulan', $month)
                    ->where('schedule_printer.tahun', $year);
            })
            ->whereNull('schedule_printer.id_printer')
            ->where('inv_printers.site', $site)
            ->select('inv_printers.id', 'inv_printers.printer_code', 'inv_printers.department', 'inv_printers.site')
            ->get();

        if ($unscheduledPrinters->isEmpty()) {
            return response()->json(['message' => 'All printers already scheduled this month'], 200);
        }

        $totalUnits = $unscheduledPrinters->count();
        $maxDays = 15;
        $maxPerDay = (int) ceil($totalUnits / $maxDays);

        /** -----------------------------------------
         *  GROUP & SORT DEPARTMENTS (MOST → FEWEST)
         *  ----------------------------------------- */
        $groupedByDept = $unscheduledPrinters
            ->groupBy('department')
            ->sortByDesc(fn($group) => $group->count());

        /** -----------------------------------------
         *  PREPARE DAYS 1–15 WITH CAPACITY
         *  ----------------------------------------- */
        $days = [];
        for ($d = 1; $d <= 15; $d++) {
            $date = Carbon::create($year, $month, $d)->toDateString();
            $days[$date] = [
                'count' => 0,
                'items' => []
            ];
        }

        $schedule = [];

        /** -----------------------------------------
         *  SCHEDULING ENGINE
         *  ----------------------------------------- */
        foreach ($groupedByDept as $dept => $printers) {

            foreach ($printers as $printer) {

                foreach ($days as $date => &$day) {

                    // Max 10 inspections per day
                    if ($day['count'] >= $maxPerDay) {
                        continue;
                    }

                    // Same department priority on same day
                    $deptAlreadyOnDay = collect($day['items'])
                        ->contains(fn($item) => $item['dept'] === $dept);

                    // Allow if same dept OR day is empty
                    if ($deptAlreadyOnDay || $day['count'] === 0 || $day['count'] < 10) {

                        $schedule[] = [
                            'id_printer' => $printer->id,
                            'tanggal_inspection' => $date,
                            'bulan' => $month,
                            'tahun' => $year,
                            'printer_code' => $printer->printer_code,
                            'dept' => $dept,
                            'site' => $printer->site,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        $day['items'][] = [
                            'dept' => $dept,
                            'printer_id' => $printer->id
                        ];

                        $day['count']++;

                        continue 2; // move to next printer
                    }
                }
            }
        }

        if (empty($schedule)) {
            return response()->json(['error' => 'Unable to schedule printers within 15 days capacity'], 422);
        }

        DB::table('schedule_printer')->insert($schedule);

        return response()->json([
            'message' => 'Monthly schedule generated successfully',
            'month' => $month,
            'year' => $year,
            'total_scheduled' => count($schedule),
        ], 201);
    }

}
