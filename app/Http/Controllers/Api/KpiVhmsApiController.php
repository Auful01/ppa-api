<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DispatchVhms;
use App\Models\kpiVhms;
use App\Services\ImageOptimizerService;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KpiVhmsApiController extends Controller
{
    private ?bool $hasFeedbackColumn = null;

    public function index(Request $request)
    {
        $today = Carbon::today();
        $start = $today->copy()->startOfMonth();
        $end = $today->copy()->endOfDay();
        $site = $this->resolveSite($request);

        return response()->json([
            'data' => $this->buildChartPayload($start, $end, $site, 'Month'),
            'meta' => [
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'site' => $site,
            ],
        ]);
    }

    public function filter(Request $request)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'string', 'in:Month,Year'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'min:2000'],
            'site' => ['nullable', 'string'],
        ]);

        $type = $validated['type'] ?? 'Month';
        $today = Carbon::today();

        if ($type === 'Month' && ! empty($validated['month']) && ! empty($validated['year'])) {
            $start = Carbon::create((int) $validated['year'], (int) $validated['month'], 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
        } elseif ($type === 'Year' && ! empty($validated['year'])) {
            $start = Carbon::create((int) $validated['year'], 1, 1)->startOfDay();
            $end = Carbon::create((int) $validated['year'], 12, 31)->endOfDay();
        } else {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfDay();
        }

        $site = $this->resolveSite($request);

        return response()->json([
            'data' => $this->buildChartPayload($start, $end, $site, $type),
            'meta' => [
                'type' => $type,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'site' => $site,
            ],
        ]);
    }

    public function updateFeedback(Request $request)
    {
        if (! $this->supportsFeedback()) {
            return response()->json([
                'message' => 'Kolom feedback belum tersedia pada tabel historical_vhms_downloads.',
            ], 422);
        }

        $validated = $request->validate([
            'id' => ['required'],
            'feedback' => ['nullable', 'string'],
        ]);

        $feedback = $validated['feedback'] === 'DELETE_FEEDBACK' ? null : $validated['feedback'];

        SiteContext::authorizeWrite($request);

        $query = DB::table('historical_vhms_downloads')->where('id', $validated['id']);
        if (! SiteContext::canAccessAnySite($request)) {
            $query->where('site', $this->resolveSite($request));
        }

        abort_if(! (clone $query)->exists(), 404);
        $query->update(['feedback' => $feedback]);

        return response()->json([
            'message' => 'Feedback updated successfully.',
        ]);
    }

    public function store(Request $request)
    {
        SiteContext::authorizeWrite($request, $this->resolveSite($request));

        $validated = $request->validate([
            'id' => ['nullable'],
            'week_data' => ['required', 'string'],
            'status' => ['nullable', 'array'],
            'status.*' => ['nullable', 'string'],
            'pic' => ['nullable', 'array'],
            'pic.*' => ['nullable', 'string'],
            'remark' => ['nullable', 'array'],
            'remark.*' => ['nullable', 'string'],
            'unit_code' => ['nullable', 'array'],
            'unit_code.*' => ['nullable', 'string'],
            'evidence_image' => ['nullable', 'file', 'image'],
        ]);

        $currentDate = Carbon::now();
        $year = (int) $currentDate->format('Y');
        $month = (int) $currentDate->month;
        $result = [];

        if ($request->hasFile('evidence_image')) {
            $pathEvidenceImage = ImageOptimizerService::storeAndOptimize($request->file('evidence_image'), 'images');
            $result['insertDataEvidenceImage'] = DB::table('kpi_vhms_evidence')->insert([
                'week_data' => $validated['week_data'],
                'evidence_image' => 'storage/' . $pathEvidenceImage,
                'month' => $month,
                'year' => $year,
            ]);
        }

        if (empty($validated['id'])) {
            $rows = [];
            foreach ($validated['unit_code'] ?? [] as $key => $code) {
                $rows[] = [
                    'unit_code' => $code,
                    'week_data' => $validated['week_data'],
                    'status' => $validated['status'][$key] ?? null,
                    'pic' => $validated['pic'][$key] ?? null,
                    'remark' => $validated['remark'][$key] ?? null,
                    'month' => $month,
                    'year' => $year,
                    'created_by' => $request->user()->name ?? 'API',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $result['createKpiBreakdownUnit'] = ! empty($rows)
                ? DB::table('kpi_vhms')->insert($rows)
                : false;
        } else {
            $result['updateKpiBreakdownUnit'] = kpiVhms::query()
                ->whereKey($validated['id'])
                ->update($request->except(['evidence_image']));
        }

        return response()->json([
            'message' => 'KPI VHMS saved successfully.',
            'data' => $result,
        ], 201);
    }

    public function breakdown(Request $request)
    {
        $validated = $request->validate([
            'week_data' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2000'],
        ]);

        $data = kpiVhms::query()
            ->where('week_data', $validated['week_data'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->get();

        return response()->json([
            'data' => $data,
        ]);
    }

    public function summary(Request $request)
    {
        $validated = $request->validate([
            'week_data' => ['required', 'string'],
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2000'],
        ]);

        $totalUnit = DispatchVhms::count();
        $totalBreakdown = kpiVhms::query()
            ->where('week_data', $validated['week_data'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->count();

        $evidenceImage = DB::table('kpi_vhms_evidence')
            ->where('week_data', $validated['week_data'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->first();

        $dataBreakdown = kpiVhms::query()
            ->where('week_data', $validated['week_data'])
            ->where('month', $validated['month'])
            ->where('year', $validated['year'])
            ->get();

        $monthLabel = $this->indonesianMonth((int) $validated['month']);
        $totalDownloaded = $totalUnit - $totalBreakdown;
        $percentageBreakdown = $totalUnit > 0 ? ($totalBreakdown / $totalUnit) * 100 : 0;
        $percentageReady = $totalUnit > 0 ? ($totalDownloaded / $totalUnit) * 100 : 0;

        return response()->json([
            'data' => [
                'periode' => $validated['week_data'] . ' ' . $monthLabel . ' ' . $validated['year'],
                'total_unit' => $totalUnit,
                'vhms_unit_terdownload' => $totalDownloaded,
                'vhms_unit_breakdown' => $totalBreakdown,
                'percentageVhmsTerDownload' => $percentageReady,
                'percentageVhmsBreakdown' => $percentageBreakdown,
                'achhievement' => $percentageReady,
                'evidence_image' => $evidenceImage->evidence_image ?? '',
                'dataTableBreakdown' => $dataBreakdown->map(fn ($item) => [
                    'unit_code' => $item->unit_code,
                    'pic' => $item->pic,
                    'remark' => $item->remark,
                ])->values(),
            ],
        ]);
    }

    private function buildChartPayload(Carbon $start, Carbon $end, string $site, string $type): array
    {
        $groups = [
            'HD' => ['HD785-7'],
            'PC1250' => ['PC1250-8R', 'PC1250-11'],
            'PC2000' => ['PC2000-8', 'PC2000-11R'],
        ];

        $allModels = array_merge(...array_values($groups));

        $rows = DB::table('historical_vhms_downloads')
            ->selectRaw('DATE(`date`) as day, model, status, COUNT(*) as cnt')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereIn('model', $allModels)
            ->where('site', $site)
            ->when($this->supportsFeedback(), function ($query) {
                $query->whereNull('feedback');
            })
            ->groupBy('day', 'model', 'status')
            ->orderBy('day', 'asc')
            ->get();

        $period = [];
        $cursor = $start->copy();
        if ($type === 'Year') {
            while ($cursor->lte($end)) {
                $period[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }
        } else {
            while ($cursor->lte($end)) {
                $period[] = $cursor->toDateString();
                $cursor->addDay();
            }
        }

        $counts = [];
        foreach ($rows as $row) {
            $groupName = null;
            foreach ($groups as $name => $models) {
                if (in_array($row->model, $models, true)) {
                    $groupName = $name;
                    break;
                }
            }

            if (! $groupName) {
                continue;
            }

            $key = $type === 'Year'
                ? Carbon::parse($row->day)->format('Y-m')
                : $row->day;

            $counts[$key][$groupName][$row->status][] = (int) $row->cnt;
        }

        $payload = [
            'HD' => ['update' => [], 'waiting' => [], 'not_update' => [], 'kosong' => []],
            'PC1250' => ['update' => [], 'waiting' => [], 'not_update' => [], 'kosong' => []],
            'PC2000' => ['update' => [], 'waiting' => [], 'not_update' => [], 'kosong' => []],
            'categories' => [],
        ];

        foreach ($period as $key) {
            $payload['categories'][] = $type === 'Year'
                ? $this->indonesianMonth((int) explode('-', $key)[1])
                : Carbon::parse($key)->format('j-n-Y');

            foreach (array_keys($groups) as $groupName) {
                foreach (['update', 'waiting', 'not_update'] as $status) {
                    if ($type === 'Year') {
                        $values = $counts[$key][$groupName][$status] ?? [];
                        $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
                        $payload[$groupName][$status][] = round($avg, 0);
                    } else {
                        $payload[$groupName][$status][] = $counts[$key][$groupName][$status][0] ?? 0;
                    }
                }

                $payload[$groupName]['kosong'][] = 0;
            }
        }

        $selectColumns = [
            'id',
            'sn',
            'cn',
            'model',
            'status',
            'last_download',
            'last_operation',
            'pld_last_record',
            'trend_last_record',
            'fault_last_record',
            'his_last_record',
            'date',
        ];

        if ($this->supportsFeedback()) {
            $selectColumns[] = 'feedback';
        }

        $payload['vhmsNotDownload'] = DB::table('historical_vhms_downloads')
            ->select($selectColumns)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('site', $site)
            ->where('status', 'not_update')
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'sn' => $item->sn,
                'cn' => $item->cn,
                'model' => $item->model,
                'status' => $item->status,
                'feedback' => $item->feedback ?? '',
                'last_dnld' => $item->last_download ?: '-',
                'last_operation' => $item->last_operation ?: '-',
                'last_payload' => $item->pld_last_record ?: '-',
                'last_trend' => $item->trend_last_record ?: '-',
                'last_fault' => $item->fault_last_record ?: '-',
                'last_his' => $item->his_last_record ?: '-',
                'date' => $item->date,
            ])->values();

        return $payload;
    }

    private function resolveSite(Request $request): string
    {
        // SOURCE OF TRUTH: KpiVhmsController hardcodes `$site = 'BIB'` in both
        // index() and getDataFilter(). VHMS download monitoring exists only for
        // BIB, so the mobile endpoint must pin to BIB regardless of the caller's
        // active site (otherwise a non-BIB active site returns empty data while
        // the web still shows BIB).
        return 'BIB';
    }

    private function indonesianMonth(int $month): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ][$month] ?? (string) $month;
    }

    private function supportsFeedback(): bool
    {
        return $this->hasFeedbackColumn ??= Schema::hasColumn('historical_vhms_downloads', 'feedback');
    }
}
