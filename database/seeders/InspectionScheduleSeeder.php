<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectionScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLaptopSchedules();
        $this->seedComputerSchedules();
        $this->seedPrinterSchedules();
        $this->seedMobileTowerSchedules();
    }

    private function seedLaptopSchedules(): void
    {
        if (! Schema::hasTable('schedule_laptop') || ! Schema::hasTable('inv_laptops')) {
            return;
        }

        $laptops = DB::table('inv_laptops')->limit(3)->get();

        if ($laptops->isEmpty()) {
            return;
        }

        DB::table('schedule_laptop')
            ->whereIn('laptop_code', $laptops->pluck('laptop_code')->filter()->values())
            ->whereBetween('tanggal_inspection', ['2026-05-01', '2026-05-31'])
            ->delete();

        $rows = $laptops->values()->map(function ($laptop, int $index) {
            return $this->filterColumns('schedule_laptop', [
                'id_laptop' => $laptop->id,
                'tanggal_inspection' => Carbon::create(2026, 5, 11 + $index)->toDateString(),
                'actual_inspection' => $index === 0 ? Carbon::create(2026, 5, 11)->toDateString() : null,
                'bulan' => 5,
                'tahun' => 2026,
                'laptop_code' => $laptop->laptop_code ?? 'SEED-LAPTOP-' . ($index + 1),
                'dept' => $laptop->dept ?? 'ICT',
                'site' => $laptop->site ?? 'MIFA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        })->all();

        $this->insertRows('schedule_laptop', $rows);
    }

    private function seedComputerSchedules(): void
    {
        if (! Schema::hasTable('schedule_computer') || ! Schema::hasTable('inv_computers')) {
            return;
        }

        $computers = DB::table('inv_computers')->limit(3)->get();

        if ($computers->isEmpty()) {
            return;
        }

        DB::table('schedule_computer')
            ->whereIn('computer_code', $computers->pluck('computer_code')->filter()->values())
            ->where('quarter', 'Q2')
            ->where('tahun', 2026)
            ->delete();

        $rows = $computers->values()->map(function ($computer, int $index) {
            return $this->filterColumns('schedule_computer', [
                'id_computer' => $computer->id,
                'tanggal_inspection' => Carbon::create(2026, 5, 14 + $index)->toDateString(),
                'actual_inspection' => $index === 0 ? Carbon::create(2026, 5, 14)->toDateString() : null,
                'bulan' => 5,
                'tahun' => 2026,
                'quarter' => 'Q2',
                'computer_code' => $computer->computer_code ?? 'SEED-COMPUTER-' . ($index + 1),
                'dept' => $computer->dept ?? 'ICT',
                'site' => $computer->site ?? 'MIFA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        })->all();

        $this->insertRows('schedule_computer', $rows);
    }

    private function seedPrinterSchedules(): void
    {
        if (! Schema::hasTable('schedule_printer') || ! Schema::hasTable('inv_printers')) {
            return;
        }

        $printers = DB::table('inv_printers')->limit(3)->get();

        if ($printers->isEmpty()) {
            return;
        }

        DB::table('schedule_printer')
            ->whereIn('printer_code', $printers->pluck('printer_code')->filter()->values())
            ->where('bulan', 5)
            ->where('tahun', 2026)
            ->delete();

        $rows = $printers->values()->map(function ($printer, int $index) {
            return $this->filterColumns('schedule_printer', [
                'id_printer' => $printer->id,
                'tanggal_inspection' => Carbon::create(2026, 5, 17 + $index)->toDateString(),
                'actual_inspection' => $index === 0 ? Carbon::create(2026, 5, 17)->toDateString() : null,
                'bulan' => 5,
                'tahun' => 2026,
                'printer_code' => $printer->printer_code ?? 'SEED-PRINTER-' . ($index + 1),
                'dept' => $printer->department ?? $printer->division ?? 'ICT',
                'site' => $printer->site ?? 'MIFA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        })->all();

        $this->insertRows('schedule_printer', $rows);
    }

    private function seedMobileTowerSchedules(): void
    {
        if (! Schema::hasTable('schedule_mobile_tower') || ! Schema::hasTable('inv_mobile_towers')) {
            return;
        }

        $mobileTowers = DB::table('inv_mobile_towers')->limit(3)->get();

        if ($mobileTowers->isEmpty()) {
            return;
        }

        DB::table('schedule_mobile_tower')
            ->whereIn('mobile_tower_code', $mobileTowers->pluck('mt_code')->filter()->values())
            ->where('bulan', 5)
            ->where('tahun', 2026)
            ->delete();

        $rows = $mobileTowers->values()->map(function ($mobileTower, int $index) {
            return $this->filterColumns('schedule_mobile_tower', [
                'id_mobile_tower' => $mobileTower->id,
                'tanggal_inspection' => Carbon::create(2026, 5, 20 + $index)->toDateString(),
                'actual_inspection' => $index === 0 ? Carbon::create(2026, 5, 20)->toDateString() : null,
                'bulan' => 5,
                'tahun' => 2026,
                'mobile_tower_code' => $mobileTower->mt_code ?? $mobileTower->inventory_number ?? 'SEED-MT-' . ($index + 1),
                'dept' => 'ICT',
                'site' => $mobileTower->site ?? 'MIFA',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        })->all();

        $this->insertRows('schedule_mobile_tower', $rows);
    }

    private function filterColumns(string $table, array $row): array
    {
        $columns = array_flip(Schema::getColumnListing($table));

        return array_intersect_key($row, $columns);
    }

    private function insertRows(string $table, array $rows): void
    {
        if (empty($rows)) {
            return;
        }

        $columns = [];

        foreach ($rows as $row) {
            $columns = array_unique(array_merge($columns, array_keys($row)));
        }

        $normalizedRows = array_map(function (array $row) use ($columns) {
            return array_merge(array_fill_keys($columns, null), $row);
        }, $rows);

        DB::table($table)->insert($normalizedRows);
    }
}
