<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class insertScheduleLaptop extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scheduleLaptop:cron';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Insert schedule inspection Laptop every year';

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

            $unscheduledLaptops = DB::table('inv_laptops')
                ->leftJoin('schedule_laptop', function ($join) use ($year) {
                    $join->on('inv_laptops.id', '=', 'schedule_laptop.id_laptop')
                        ->where('schedule_laptop.tahun', $year);
                })
                ->whereNull('schedule_laptop.id_laptop')
                ->where('inv_laptops.site', $site)
                // ->where('inv_laptops.site', 'bib')
                ->select('inv_laptops.id', 'inv_laptops.laptop_code', 'inv_laptops.dept', 'inv_laptops.site')
                ->get();

            if ($unscheduledLaptops->isEmpty()) {
                continue;
            }

            // Group and sort departments
            $groupedByDept = $unscheduledLaptops->groupBy('dept');
            $sortedDepts = $groupedByDept->sortByDesc(fn($group) => $group->count());

            // Get department names
            $departments = $sortedDepts->keys()->values();
            $deptCount = $departments->count();

            if ($deptCount > 12) {
                continue;
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
                    continue;
                }
            }

            foreach ($departments as $index => $deptName) {
                $month = $assignedMonths[$index];
                $start = Carbon::create($year, $month, 1);
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
        }

        $this->info('Data schedule inspection Laptop successfully imported to database.');
    }
}
