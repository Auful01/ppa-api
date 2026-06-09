<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class insertScheduleMT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduleMT:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert schedule inspection Mobile Tower every month';

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
                continue;
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

            if (empty($schedule)) {
                continue;
            }

            DB::table('schedule_mobile_tower')->insert($schedule);
        }

        $this->info('Data schedule inspection Mobile Tower successfully imported to database.');
    }
}
