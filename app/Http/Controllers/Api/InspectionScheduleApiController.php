<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectionScheduleApiController extends Controller
{
    public function index(Request $request)
    {
        $site = $this->resolveSite($request);
        $device = (string) $request->input('device', 'laptop');
        $this->authorizeSite($request, $site, false);

        $currentMonth = now()->month;
        $currentQuarter = match (true) {
            $currentMonth <= 3 => 'Q1',
            $currentMonth <= 6 => 'Q2',
            $currentMonth <= 9 => 'Q3',
            default => 'Q4',
        };
        $year = (int) $request->integer('year', now()->year);
        $month = $request->filled('month') ? (int) $request->integer('month') : null;
        $quarter = $request->string('quarter')->trim()->upper()->value() ?: null;

        [$schedules, $summary, $stats] = match ($device) {
            'computer' => $this->computerData($site, $year, $month, $quarter),
            'printer' => $this->printerData($site, $year, $month),
            'mobile_tower' => $this->mobileTowerData($site, $year, $month),
            default => $this->laptopData($site, $year, $month),
        };

        $total = max((int) ($stats->total ?? 0), 1);
        $sudahSesuai = (int) ($stats->sudahSesuai ?? 0);
        $belumSesuai = (int) ($stats->belumSesuai ?? 0);

        return response()->json([
            'data' => [
                'schedules' => $schedules,
                'summary' => $summary,
                'sudahSesuai' => round(($sudahSesuai / $total) * 100, 2),
                'belumSesuai' => round(($belumSesuai / $total) * 100, 2),
            ],
            'meta' => [
                'site' => $site,
                'device' => $device,
                'year' => $year,
                'month' => $month ?? $currentMonth,
                'quarter' => $quarter ?: $currentQuarter,
            ],
        ]);
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'tanggal_inspection' => ['required', 'date'],
            'kategori' => ['required', 'string', 'in:laptop,computer,printer,mobile_tower'],
            'site' => ['nullable', 'string'],
        ]);

        $site = $this->resolveSite($request);
        $this->authorizeSite($request, $site, true);

        $date = Carbon::parse($validated['tanggal_inspection']);
        $table = match ($validated['kategori']) {
            'computer' => 'schedule_computer',
            'printer' => 'schedule_printer',
            'mobile_tower' => 'schedule_mobile_tower',
            default => 'schedule_laptop',
        };

        $query = DB::table($table)
            ->where('id', $id)
            ->where('site', $site);

        abort_if(! (clone $query)->exists(), 404);

        $query->update([
                'tanggal_inspection' => $date->toDateString(),
                'bulan' => $date->format('m'),
                'tahun' => $date->format('Y'),
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Schedule updated successfully.',
        ]);
    }

    private function authorizeSite(Request $request, string $site, bool $write): void
    {
        $user = $request->user();

        if ($write) {
            SiteContext::authorizeWrite($request);

            if (! SiteContext::canAccessAnySite($request) && $site !== $user->site) {
                abort(403, 'You dont have permission to access this page.');
            }
            return;
        }

        if (! SiteContext::canAccessAnySite($request) && $site !== $user->site) {
            abort(403, 'You dont have permission to access this page.');
        }
    }

    private function resolveSite(Request $request): string
    {
        return SiteContext::resolve($request) ?? 'HO';
    }

    private function laptopData(string $site, int $year, ?int $month = null): array
    {
        $hasActualInspection = Schema::hasColumn('schedule_laptop', 'actual_inspection');
        $hasYearColumn = Schema::hasColumn('schedule_laptop', 'tahun');

        $selects = [
            'schedule_laptop.id',
            'schedule_laptop.tanggal_inspection',
            'inv_laptops.laptop_code AS device_code',
            'inv_laptops.dept',
            'inv_laptops.site',
        ];

        if ($hasActualInspection) {
            $selects[] = 'schedule_laptop.actual_inspection';
        } else {
            $selects[] = DB::raw('NULL AS actual_inspection');
        }

        $schedules = DB::table('schedule_laptop')
            ->join('inv_laptops', 'schedule_laptop.id_laptop', '=', 'inv_laptops.id')
            ->select($selects)
            ->where('schedule_laptop.site', $site)
            ->when(
                $hasYearColumn,
                fn ($query) => $query->where('schedule_laptop.tahun', $year),
                fn ($query) => $query->whereYear('schedule_laptop.tanggal_inspection', $year)
            )
            ->when($month !== null, fn ($query) => $query->whereMonth('schedule_laptop.tanggal_inspection', $month))
            ->get();

        $summary = collect($schedules)->groupBy(fn ($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
            ->map(fn ($group) => [
                'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                'departments' => $group->pluck('dept')->unique()->values(),
            ])->values();

        $stats = $hasActualInspection
            ? DB::table('schedule_laptop')
                ->where('site', $site)
                ->when(
                    $hasYearColumn,
                    fn ($query) => $query->where('tahun', $year),
                    fn ($query) => $query->whereYear('tanggal_inspection', $year)
                )
                ->when($month !== null, fn ($query) => $query->whereMonth('tanggal_inspection', $month))
                ->whereNotNull('actual_inspection')
                ->selectRaw("SUM(CASE WHEN DATE(actual_inspection) = DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS sudahSesuai, SUM(CASE WHEN DATE(actual_inspection) != DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS belumSesuai, COUNT(*) AS total")
                ->first()
            : (object) [
                'sudahSesuai' => 0,
                'belumSesuai' => 0,
                'total' => $schedules->count(),
            ];

        return [$schedules, $summary, $stats];
    }

    private function computerData(string $site, int $year, ?int $month = null, ?string $quarter = null): array
    {
        $schedules = DB::table('schedule_computer')
            ->join('inv_computers', 'schedule_computer.id_computer', '=', 'inv_computers.id')
            ->select('schedule_computer.id', 'schedule_computer.tanggal_inspection', 'schedule_computer.actual_inspection', 'inv_computers.computer_code AS device_code', 'inv_computers.dept', 'inv_computers.site')
            ->where('schedule_computer.site', $site)
            ->where('schedule_computer.tahun', $year)
            ->when($month !== null, fn ($query) => $query->whereMonth('schedule_computer.tanggal_inspection', $month))
            ->when($month === null && $quarter !== null, fn ($query) => $query->where('schedule_computer.quarter', $quarter))
            ->get();

        $summary = collect($schedules)->groupBy(fn ($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
            ->map(fn ($group) => [
                'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                'departments' => $group->pluck('dept')->unique()->values(),
            ])->values();

        $stats = DB::table('schedule_computer')
            ->where('site', $site)
            ->where('tahun', $year)
            ->when($month !== null, fn ($query) => $query->whereMonth('tanggal_inspection', $month))
            ->when($month === null && $quarter !== null, fn ($query) => $query->where('quarter', $quarter))
            ->whereNotNull('actual_inspection')
            ->selectRaw("SUM(CASE WHEN DATE(actual_inspection) = DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS sudahSesuai, SUM(CASE WHEN DATE(actual_inspection) != DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS belumSesuai, COUNT(*) AS total")
            ->first();

        return [$schedules, $summary, $stats];
    }

    private function printerData(string $site, int $year, ?int $month = null): array
    {
        $schedules = DB::table('schedule_printer')
            ->join('inv_printers', 'schedule_printer.id_printer', '=', 'inv_printers.id')
            ->select('schedule_printer.id', 'schedule_printer.tanggal_inspection', 'schedule_printer.actual_inspection', 'inv_printers.printer_code AS device_code', 'inv_printers.department AS dept', 'inv_printers.site')
            ->where('schedule_printer.site', $site)
            ->where('schedule_printer.tahun', $year)
            ->when($month !== null, fn ($query) => $query->where('schedule_printer.bulan', $month))
            ->get();

        $summary = collect($schedules)->groupBy(fn ($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
            ->map(fn ($group) => [
                'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                'departments' => $group->pluck('dept')->unique()->values(),
            ])->values();

        $stats = DB::table('schedule_printer')
            ->where('site', $site)
            ->where('tahun', $year)
            ->when($month !== null, fn ($query) => $query->where('bulan', $month))
            ->whereNotNull('actual_inspection')
            ->selectRaw("SUM(CASE WHEN DATE(actual_inspection) = DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS sudahSesuai, SUM(CASE WHEN DATE(actual_inspection) != DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS belumSesuai, COUNT(*) AS total")
            ->first();

        return [$schedules, $summary, $stats];
    }

    private function mobileTowerData(string $site, int $year, ?int $month = null): array
    {
        $schedules = DB::table('schedule_mobile_tower')
            ->join('inv_mobile_towers', 'schedule_mobile_tower.id_mobile_tower', '=', 'inv_mobile_towers.id')
            ->select('schedule_mobile_tower.id', 'schedule_mobile_tower.tanggal_inspection', 'schedule_mobile_tower.actual_inspection', 'inv_mobile_towers.mt_code AS device_code', 'schedule_mobile_tower.site')
            ->where('schedule_mobile_tower.site', $site)
            ->where('schedule_mobile_tower.tahun', $year)
            ->when($month !== null, fn ($query) => $query->where('schedule_mobile_tower.bulan', $month))
            ->get();

        $summary = collect($schedules)->groupBy(fn ($item) => Carbon::parse($item->tanggal_inspection)->format('F'))
            ->map(fn ($group) => [
                'month' => Carbon::parse($group->first()->tanggal_inspection)->format('F'),
                'departments' => [],
            ])->values();

        $stats = DB::table('schedule_mobile_tower')
            ->where('site', $site)
            ->where('tahun', $year)
            ->when($month !== null, fn ($query) => $query->where('bulan', $month))
            ->whereNotNull('actual_inspection')
            ->selectRaw("SUM(CASE WHEN DATE(actual_inspection) = DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS sudahSesuai, SUM(CASE WHEN DATE(actual_inspection) != DATE(tanggal_inspection) THEN 1 ELSE 0 END) AS belumSesuai, COUNT(*) AS total")
            ->first();

        return [$schedules, $summary, $stats];
    }
}
