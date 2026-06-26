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

class KpiInspeksiApiController extends Controller
{
    private const DEVICE_MODELS = [
        'laptop'       => InspeksiLaptop::class,
        'computer'     => InspeksiComputer::class,
        'printer'      => InspeksiPrinter::class,
        'mobile_tower' => InspeksiMobileTower::class,
    ];

    public function index(Request $request)
    {
        $validated = $request->validate([
            'device_type' => ['nullable', 'string', 'in:laptop,computer,printer,mobile_tower'],
            'year'        => ['nullable', 'integer', 'min:2000'],
            'quarter'     => ['nullable', 'integer', 'min:1', 'max:4'],
            'month'       => ['nullable', 'integer', 'min:1', 'max:12'],
            'site'        => ['nullable', 'string'],
        ]);

        $site       = SiteContext::resolve($request);
        $deviceType = strtolower($validated['device_type'] ?? 'laptop');
        $year       = (int) ($validated['year'] ?? Carbon::now()->year);
        $quarter    = $validated['quarter'] ?? null;
        $month      = $validated['month'] ?? null;

        $modelClass = self::DEVICE_MODELS[$deviceType] ?? InspeksiLaptop::class;

        // Summary card (Total / Sudah / Belum / Achievement).
        //
        // ROOT CAUSE of the "Total 132 vs aktual ~67" Mobile Tower bug: this used
        // to scope the summary to the whole YEAR (`where('year', $year)` only).
        // MT/Printer are inspected MONTHLY and Computer QUARTERLY, so a year scope
        // counts the SAME unit once per month/quarter → ~2 months × 67 units ≈ 132,
        // while the chart (single current period) correctly showed 67 → 100%.
        //
        // Fix: scope the summary to the SAME period the chart uses per device, so
        // the card always agrees with the graph:
        //   laptop        → year (yearly inspection: 1 row/unit/year)
        //   computer      → current/selected quarter (1 row/unit/quarter)
        //   printer / MT  → current/selected month   (1 row/unit/month)
        // SCRAP is excluded from the denominator (client requirement + web parity).
        $summaryBase = function () use ($modelClass, $site, $deviceType, $year, $quarter, $month) {
            $q = $modelClass::where('site', $site)->where('year', $year);
            if ($deviceType === 'computer') {
                $q->where('triwulan', $quarter ?: ((int) ceil(Carbon::now()->month / 3)));
            } elseif ($deviceType === 'printer' || $deviceType === 'mobile_tower') {
                $q->where('month', $month ?: Carbon::now()->month);
            }
            return $this->applyScrapFilter($q, $deviceType);
        };

        $total  = (clone $summaryBase())->count();
        $sudah  = (clone $summaryBase())->where('inspection_status', 'Y')->count();
        $belum  = (clone $summaryBase())->where('inspection_status', 'N')->count();

        $percentSudah = $total > 0 ? round(($sudah / $total) * 100, 2) : 0;
        $percentBelum = $total > 0 ? round(($belum / $total) * 100, 2) : 0;

        // SOURCE OF TRUTH: KpiInspeksiController::countKpi builds the chart with a
        // DEVICE-SPECIFIC dimension (not a uniform multi-year chart):
        //   LAPTOP       -> per year
        //   COMPUTER     -> per quarter (Q1-Q4); per month (12) when site == ADW;
        //                   single quarter/month when quarter/month is supplied
        //   PRINTER      -> single month label "{Bulan} {year}"
        //   MOBILE TOWER -> single month label "{Bulan} {year}"
        [$chartLabels, $chartSudah, $chartBelum] =
            $this->buildChart($modelClass, $site, $deviceType, $year, $quarter, $month);

        return response()->json([
            'data' => [
                'device_type'   => $deviceType,
                'year'          => $year,
                'total'         => $total,
                'sudah'         => $sudah,
                'belum'         => $belum,
                'percent_sudah' => $percentSudah,
                'percent_belum' => $percentBelum,
                'chart_labels'  => $chartLabels,
                'chart_sudah'   => $chartSudah,
                'chart_belum'   => $chartBelum,
            ],
            'meta' => [
                'site'        => $site,
                'device_types' => array_keys(self::DEVICE_MODELS),
            ],
        ]);
    }

    /**
     * Exclude SCRAP assets from an inspeksi query, device-aware, mirroring
     * KpiInspeksiController::countKpi:
     *   computer/laptop/printer -> inventory_status != 'SCRAP'
     *     (printer additionally requires a real inspection_status: not null, not '-')
     *   mobile_tower            -> related inventory mt.status != 'SCRAP'
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyScrapFilter($query, string $deviceType)
    {
        if ($deviceType === 'mobile_tower') {
            return $query->whereHas('mt', function ($q) {
                $q->where('status', '!=', 'SCRAP');
            });
        }

        $query->where('inventory_status', '!=', 'SCRAP');

        if ($deviceType === 'printer') {
            $query->whereNotNull('inspection_status')->where('inspection_status', '!=', '-');
        }

        return $query;
    }

    /**
     * Mirror of KpiInspeksiController::countKpi chart construction.
     *
     * @return array{0: array<int,string>, 1: array<int,float>, 2: array<int,float>}
     */
    private function buildChart(string $modelClass, ?string $site, string $deviceType, int $year, ?int $quarter, ?int $month): array
    {
        $labels = [];
        $sudah  = [];
        $belum  = [];

        $push = function (string $label, $countAll, $countY, $countN) use (&$labels, &$sudah, &$belum) {
            $labels[] = $label;
            $sudah[]  = $countAll ? round(($countY / $countAll) * 100, 2) : 0;
            $belum[]  = $countAll ? round(($countN / $countAll) * 100, 2) : 0;
        };

        $counts = function (array $where) use ($modelClass, $site, $deviceType) {
            $base = $modelClass::where('site', $site);
            foreach ($where as $col => $val) {
                $base->where($col, $val);
            }
            // Exclude SCRAP from every period bucket too (web parity).
            $this->applyScrapFilter($base, $deviceType);
            return [
                (clone $base)->count(),
                (clone $base)->where('inspection_status', 'Y')->count(),
                (clone $base)->where('inspection_status', 'N')->count(),
            ];
        };

        if ($deviceType === 'laptop') {
            // Web parity (KpiInspeksiController::countKpi): when a year is supplied
            // it sets $tahunList = [(int)$request->year] — a SINGLE year. The year
            // is only a filter, NOT a per-bar chart dimension. So we emit exactly
            // ONE group for the selected year => two bars rendered client-side
            // (Sudah % green / Belum % red). No multi-year (2025/2026) categories.
            [$a, $y, $n] = $counts(['year' => $year]);
            $push((string) $year, $a, $y, $n);
        } elseif ($deviceType === 'computer') {
            if ($quarter && $year) {
                [$a, $y, $n] = $counts(['year' => $year, 'triwulan' => $quarter]);
                $push("Q{$quarter}", $a, $y, $n);
            } elseif ($month && $year) {
                [$a, $y, $n] = $counts(['year' => $year, 'month' => $month]);
                $push(Carbon::create()->month($month)->translatedFormat('F'), $a, $y, $n);
            } elseif (strtoupper((string) $site) === 'ADW') {
                foreach (range(1, 12) as $mnt) {
                    [$a, $y, $n] = $counts(['year' => $year, 'month' => $mnt]);
                    $push(Carbon::create()->month($mnt)->translatedFormat('F'), $a, $y, $n);
                }
            } else {
                foreach ([1, 2, 3, 4] as $qtr) {
                    [$a, $y, $n] = $counts(['year' => $year, 'triwulan' => $qtr]);
                    $push("Q{$qtr}", $a, $y, $n);
                }
            }
        } else {
            // printer / mobile_tower -> single month "{Bulan} {year}"
            $m = $month ?: Carbon::now()->month;
            [$a, $y, $n] = $counts(['year' => $year, 'month' => $m]);
            $monthName = Carbon::create()->month($m)->translatedFormat('F');
            $push("{$monthName} {$year}", $a, $y, $n);
        }

        return [$labels, $sudah, $belum];
    }
}
