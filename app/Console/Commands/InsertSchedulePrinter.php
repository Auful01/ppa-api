<?php

namespace App\Console\Commands;

use App\Models\InspeksiPrinter;
use App\Models\InvPrinter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class insertSchedulePrinter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schedulePrinter:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert schedule inspection printer every month';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $year = now()->year;
        $month = now()->month;

        $sites = ['ba', 'mifa', 'mhu', 'adw', 'ami', 'pik', 'bge', 'bib', 'ipt', 'mlp', 'mip', 'vib', 'sbs', 'sks'];
        // $sites = ['bib', 'mip'];

        foreach ($sites as $site) {

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
                continue;
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
                continue;
            }

            DB::table('schedule_printer')->insert($schedule);
        }

        $this->info('Data schedule inspection printer successfully imported to database.');
    }
}
