<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChartInspeksiApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $site = SiteContext::resolve($request);
        $year = (int) ($request->integer('year') ?: Carbon::now()->year);

        $chartData = [
            'labelsLaptop' => [$year],
            'persenLaptop' => [$this->percentage(InspeksiLaptop::query(), $site, $year, null)],
            'persenKomputer' => [],
            'persenPrinter' => [],
            'persenMT' => [],
            'summary' => [
                'laptop' => $this->summary(InspeksiLaptop::query(), $site, $year, null),
                'komputer' => $this->summary(InspeksiComputer::query(), $site, $year, null),
                'printer' => $this->summary(InspeksiPrinter::query(), $site, $year, null),
                'mobile_tower' => $this->summary(InspeksiMobileTower::query(), $site, $year, null),
            ],
        ];

        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $chartData['persenKomputer'][] = $this->percentage(InspeksiComputer::query(), $site, $year, [
                'triwulan' => $quarter,
            ]);
        }

        for ($month = 1; $month <= 12; $month++) {
            $chartData['persenPrinter'][] = $this->percentage(InspeksiPrinter::query(), $site, $year, [
                'month' => $month,
            ]);
            $chartData['persenMT'][] = $this->percentage(InspeksiMobileTower::query(), $site, $year, [
                'month' => $month,
            ]);
        }

        return response()->json([
            'site' => $site,
            'chartData' => $chartData,
        ]);
    }

    private function percentage($query, ?string $site, int $year, ?array $extraWhere): float
    {
        SiteContext::apply($query, 'site', $site)->where('year', $year);

        if ($extraWhere) {
            foreach ($extraWhere as $column => $value) {
                $query->where($column, $value);
            }
        }

        $total = (clone $query)->count();
        $inspected = (clone $query)->where('inspection_status', 'Y')->count();

        if ($total === 0) {
            return 0;
        }

        return round(($inspected / $total) * 100, 2);
    }

    private function summary($query, ?string $site, int $year, ?array $extraWhere): array
    {
        SiteContext::apply($query, 'site', $site)->where('year', $year);

        if ($extraWhere) {
            foreach ($extraWhere as $column => $value) {
                $query->where($column, $value);
            }
        }

        $total = (clone $query)->count();
        $inspected = (clone $query)->where('inspection_status', 'Y')->count();
        $pending = max($total - $inspected, 0);

        return [
            'total' => $total,
            'inspected' => $inspected,
            'pending' => $pending,
            'inspected_percent' => $total === 0 ? 0 : round(($inspected / $total) * 100, 2),
            'pending_percent' => $total === 0 ? 0 : round(($pending / $total) * 100, 2),
            'target_percent' => 95,
        ];
    }
}
