<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class insertScheduleComputer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduleComputer:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert schedule inspection Computer every Quarterly';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $year = now()->year;
        $month = now()->month;

        $sites = ['ba', 'mifa', 'mhu', 'adw', 'ami', 'pik', 'bge', 'bib', 'ipt', 'mlp', 'mip', 'vib', 'sbs', 'sks'];
        // $sites = ['bib'];

        foreach ($sites as $site) {

            if ($month >= 1 && $month <= 3) {
                $quarter = 'Q1';
                $start = Carbon::create($year, 1, 1);
                $end = Carbon::create($year, 3, 31);
            } elseif ($month >= 4 && $month <= 6) {
                $quarter = 'Q2';
                $start = Carbon::create($year, 4, 1);
                $end = Carbon::create($year, 6, 30);
            } elseif ($month >= 7 && $month <= 9) {
                $quarter = 'Q3';
                $start = Carbon::create($year, 7, 1);
                $end = Carbon::create($year, 9, 30);
            } else {
                $quarter = 'Q4';
                $start = Carbon::create($year, 10, 1);
                $end = Carbon::create($year, 12, 31);
            }

            // Get computers at site 'bib' that are not yet scheduled for this quarter and year
            $alreadyScheduledIds = DB::table('schedule_computer')
                ->where('tahun', $year)
                ->where('site', $site)
                ->where('quarter', $quarter)
                ->pluck('id_computer')
                ->toArray();

            $unscheduledComputers = DB::table('inv_computers')
                ->where('site', $site)
                ->whereNotIn('id', $alreadyScheduledIds)
                ->select('id', 'computer_code', 'dept', 'site')
                ->get();

            if ($unscheduledComputers->isEmpty()) {
                continue;
            }

            // Generate ~70% of the days in the quarter
            $daysInQuarter = $start->diffInDays($end) + 1;
            $dates = collect();

            for ($i = 0; $i < $daysInQuarter; $i++) {
                $dates->push($start->copy()->addDays($i)->toDateString());
            }

            $usableDates = $dates->shuffle()->take((int) round($daysInQuarter * 0.7))->values();

            $repeatDates = [];
            $computerRemaining = $unscheduledComputers->count();
            $dateIndex = 0;

            while ($computerRemaining > 0 && $dateIndex < count($usableDates)) {
                $batchSize = rand(2, 3);
                for ($i = 0; $i < $batchSize && $computerRemaining > 0; $i++) {
                    $repeatDates[] = $usableDates[$dateIndex];
                    $computerRemaining--;
                }
                $dateIndex++;
            }

            $dateIndex = 0;
            while ($computerRemaining > 0) {
                $repeatDates[] = $usableDates[$dateIndex % count($usableDates)];
                $computerRemaining--;
                $dateIndex++;
            }

            sort($repeatDates);

            // Build schedule entries
            $finalSchedule = [];
            $computerIndex = 0;

            $groupedByDept = $unscheduledComputers->groupBy('dept');
            $sortedDepts = $groupedByDept->sortByDesc(function ($group) {
                return $group->count();
            });

            // Flatten the sorted groups into one ordered list
            $sortedComputer = collect();
            foreach ($sortedDepts as $group) {
                $sortedComputer = $sortedComputer->concat($group->sortBy('computer_code'));
            }
            $sortedComputer = $sortedComputer->values();

            foreach ($sortedComputer as $computerxz) {
                $inspectionDate = Carbon::parse($repeatDates[$computerIndex]);

                $finalSchedule[] = [
                    'id_computer' => $computerxz->id,
                    'tanggal_inspection' => $inspectionDate->toDateString(),
                    'quarter' => $quarter,
                    'bulan' => $inspectionDate->format('m'),
                    'tahun' => $inspectionDate->format('Y'),
                    'computer_code' => $computerxz->computer_code,
                    'dept' => $computerxz->dept,
                    'site' => $computerxz->site,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $computerIndex++;
            }

            DB::table('schedule_computer')->insert($finalSchedule);
        }

        $this->info('Data schedule inspection Computer successfully imported to database.');
    }
}
