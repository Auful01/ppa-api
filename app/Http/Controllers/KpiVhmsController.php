<?php

namespace App\Http\Controllers;

use App\Models\DispatchVhms;
use App\Models\kpiVhms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;

class KpiVhmsController extends Controller
{
    private ?bool $hasFeedbackColumn = null;

    public function index()
    {
        $site = 'BIB';
        $auth = auth()->user();

        // 1st of this month -> today
        $today = Carbon::today();
        $start = $today->copy()->startOfMonth();
        $end = $today->copy()->endOfDay();

        // model groups
        $groups = [
            'HD' => ['HD785-7'],
            'PC1250' => ['PC1250-8R', 'PC1250-11'],
            'PC2000' => ['PC2000-8', 'PC2000-11R'],
        ];

        // flatten all relevant models for the query
        $allModels = array_merge(...array_values($groups));

        // query grouped counts by date, model, status
        $rows = DB::table('historical_vhms_downloads')
            ->selectRaw('DATE(`date`) as day, model, status, COUNT(*) as cnt')
            ->whereBetween('date', [$start->toDateString(), $today->toDateString()])
            ->whereIn('model', $allModels)
            ->where('site', $site)
            ->when($this->supportsFeedback(), function ($query) {
                $query->whereNull('feedback');
            })
            ->groupBy('day', 'model', 'status')
            ->orderBy('day', 'asc')
            ->get();

        // prepare date categories from start -> today (ascending)
        $period = [];
        $cursor = $start->copy();
        while ($cursor->lte($today)) {
            $period[] = $cursor->toDateString();
            $cursor->addDay();
        }

        // map counts into [day][group][status] => cnt
        $counts = [];
        foreach ($rows as $r) {
            // find which group this model belongs to
            $groupName = null;
            foreach ($groups as $gName => $models) {
                if (in_array($r->model, $models, true)) {
                    $groupName = $gName;
                    break;
                }
            }
            if (!$groupName)
                continue;

            $day = $r->day;
            $status = $r->status;
            $counts[$day][$groupName][$status] = (int) $r->cnt;
        }

        // build arrays per group and status (ordered by period)
        $payload = [
            'HD' => [
                'update' => [],
                'waiting' => [],
                'not_update' => [],
                'kosong' => [],
            ],
            'PC1250' => [
                'update' => [],
                'waiting' => [],
                'not_update' => [],
                'kosong' => [],
            ],
            'PC2000' => [
                'update' => [],
                'waiting' => [],
                'not_update' => [],
                'kosong' => [],
            ],
            'categories' => [],
        ];

        foreach ($period as $isoDate) {
            // categories - format "day-month-year" like your example e.g., "1-10-2025"
            $d = Carbon::parse($isoDate);
            $label = $d->day . '-' . $d->month . '-' . $d->year;
            $payload['categories'][] = $label;

            foreach (array_keys($groups) as $groupName) {
                $payload[$groupName]['update'][] = $counts[$isoDate][$groupName]['update'] ?? 0;
                $payload[$groupName]['waiting'][] = $counts[$isoDate][$groupName]['waiting'] ?? 0;
                $payload[$groupName]['not_update'][] = $counts[$isoDate][$groupName]['not_update'] ?? 0;

                // kosong = always 0 per your spec
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

        $vhmsNotDownload = DB::table('historical_vhms_downloads')
            ->select($selectColumns)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('site', $site)
            ->where('status', 'not_update')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item) {
                return [
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
                ];
            });


        $payload['vhmsNotDownload'] = $vhmsNotDownload;

        // dd($payload);

        // dd($payload);

        // return via Inertia to the Vue page
        return Inertia::render('Inventory/Kpi/KpiVhms/KpiVhmsIndex', [
            'data' => $payload,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'site' => $site,
        ]);


        // return Inertia::render(
        //     'Inventory/Kpi/KpiVhms/KpiVhmsIndex',
        //     [
        //         'chartData' => [
        //             'labels' => [],
        //             'sudah_inspeksi' => [],
        //             'belum_inspeksi' => [],
        //         ],
        //         'site' => 'BIB',
        //     ]
        // );
    }

    public function getDataFilter(Request $request)
    {
        $site = 'BIB';
        $type = $request->input('type');   // 'Month' or 'Year'
        $month = $request->input('month');
        $year = $request->input('year');

        $today = Carbon::today();

        // Determine time range
        if ($type === 'Month' && $month && $year) {
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = Carbon::create($year, $month, 1)->endOfMonth();
        } elseif ($type === 'Year' && $year) {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
        } else {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->endOfDay();
        }

        // Model groups
        $groups = [
            'HD' => ['HD785-7'],
            'PC1250' => ['PC1250-8R', 'PC1250-11'],
            'PC2000' => ['PC2000-8', 'PC2000-11R'],
        ];

        $allModels = array_merge(...array_values($groups));

        // Base query
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

        // Period categories
        $period = [];
        $cursor = $start->copy();

        if ($type === 'Month') {
            while ($cursor->lte($end)) {
                $period[] = $cursor->toDateString();
                $cursor->addDay();
            }
        } else {
            while ($cursor->lte($end)) {
                $period[] = $cursor->format('Y-m');
                $cursor->addMonth();
            }
        }

        // Grouped counts
        $counts = [];

        foreach ($rows as $r) {
            $groupName = null;
            foreach ($groups as $gName => $models) {
                if (in_array($r->model, $models, true)) {
                    $groupName = $gName;
                    break;
                }
            }
            if (!$groupName)
                continue;

            $key = $type === 'Year'
                ? Carbon::parse($r->day)->format('Y-m')
                : $r->day;

            $counts[$key][$groupName][$r->status][] = (int) $r->cnt;
        }

        // Build payload skeleton
        $payload = [
            'HD' => ['update' => [], 'waiting' => [], 'not_update' => [], 'kosong' => []],
            'PC1250' => ['update' => [], 'waiting' => [], 'not_update' => [], 'kosong' => []],
            'PC2000' => ['update' => [], 'waiting' => [], 'not_update' => [], 'kosong' => []],
            'categories' => [],
        ];

        $indonesianMonths = [
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
            12 => 'Desember'
        ];

        foreach ($period as $key) {
            if ($type === 'Month') {
                $d = Carbon::parse($key);
                $label = "{$d->day}-{$d->month}-{$d->year}";
            } else {
                [$y, $m] = explode('-', $key);
                $label = $indonesianMonths[(int) $m];
            }

            $payload['categories'][] = $label;

            foreach (array_keys($groups) as $groupName) {
                foreach (['update', 'waiting', 'not_update'] as $status) {
                    if ($type === 'Year') {
                        // Compute average per record/status across that month
                        $values = $counts[$key][$groupName][$status] ?? [];
                        $avg = count($values) > 0 ? array_sum($values) / count($values) : 0;
                        $payload[$groupName][$status][] = round($avg, 0); // ⬅️ round to 0 decimals
                    } else {
                        $payload[$groupName][$status][] = $counts[$key][$groupName][$status][0] ?? 0;
                    }
                }
                $payload[$groupName]['kosong'][] = 0;
            }

        }

        // Get not_update detail list
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

        $vhmsNotDownload = DB::table('historical_vhms_downloads')
            ->select($selectColumns)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where('site', $site)
            ->where('status', 'not_update')
            ->orderBy('date', 'desc')
            ->get()
            ->map(function ($item) {
                return [
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
                ];
            });

        $payload['vhmsNotDownload'] = $vhmsNotDownload;

        return response()->json($payload);
    }


    public function updateFeedback(Request $request)
    {
        if (! $this->supportsFeedback()) {
            return response()->json([
                'success' => false,
                'message' => 'Kolom feedback belum tersedia pada tabel historical_vhms_downloads.',
            ], 422);
        }

        $request->validate([
            'id' => 'required',
            'feedback' => 'nullable|string',
        ]);

        $feedback = $request->feedback === 'DELETE_FEEDBACK' ? null : $request->feedback;

        DB::table('historical_vhms_downloads')
            ->where('id', $request->id)
            ->update(['feedback' => $feedback]);

        return response()->json(['success' => true]);
    }

    private function supportsFeedback(): bool
    {
        return $this->hasFeedbackColumn ??= Schema::hasColumn('historical_vhms_downloads', 'feedback');
    }




    public function store(Request $request)
    {
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        if ($request->hasFile('evidence_image')) {
            $evidenceImage = $request->file('evidence_image');
            $pathEvidenceImage = $evidenceImage->store('images', 'public');
            $newPathEvidenceImage = 'storage/' . $pathEvidenceImage;

            $dataEvidenceImage = [
                'week_data' => $request->week_data,
                'evidence_image' => $newPathEvidenceImage,
                'month' => $month,
                'year' => $year,
            ];
            $data['insertDataEvidenceImage'] = DB::table('kpi_vhms_evidence')->insert($dataEvidenceImage);
        }
        $vhms_get_data = kpiVhms::find($request->id);

        if (empty($request->id)) {
            $weekData = $request->week_data;
            $status = $request->status;
            $pic = $request->pic;
            $remark = $request->remark;
            foreach ($request->unit_code as $key => $code) {
                $dataKpiVhms[] = [
                    'unit_code' => $code,
                    'week_data' => $weekData,
                    'status' => $status[$key] ?? null,
                    'pic' => $pic[$key] ?? null,
                    'remark' => $remark[$key] ?? null,
                    'month' => $month,
                    'year' => $year,
                    'created_by' => Auth::user()->name,
                ];
            }
            $data['createKpiBreakdownUnit'] = DB::table('kpi_vhms')->insert($dataKpiVhms);
            // return response()->json($dataKpiVhms, 201);
        } else {
            $data['updateKpiBreakdownUnit'] = kpiVhms::firstWhere('id', $request->id)->update($request->all());
        }
        return response()->json($data, 201);
    }

    public function showDataSortingUnitBreakdown(Request $request)
    {
        $data = kpiVhms::where('week_data', $request->week_data)->where('month', $request->month)->where('year', $request->year)->get();
        return response()->json($data);
    }

    public function showDataKpi(Request $request)
    {
        $kpi['evidenceImage'] = '';
        $arrayDataKpiVhmsTableBreakdownList = [];
        $kpi['total_unit'] = DispatchVhms::count();
        $kpi['total_breakdown'] = kpiVhms::where('week_data', $request->week_data)->where('month', $request->month)->where('year', $request->year)->count();
        $kpi['total_ter_download'] = $kpi['total_unit'] - $kpi['total_breakdown'];

        $evidenceImage = DB::table('kpi_vhms_evidence')->where('week_data', $request->week_data)->where('month', $request->month)->where('year', $request->year)->first();
        $dataBreakdown = KpiVhms::where('week_data', $request->week_data)->where('month', $request->month)->where('year', $request->year)->get();
        if (!empty($dataBreakdown)) {
            foreach ($dataBreakdown as $data) {

                $arrayDataKpiVhmsTableBreakdownList[] = [
                    'unit_code' => $data->unit_code,
                    'pic' => $data->pic,
                    'remark' => $data->remark,
                ];
            }
        }

        if (!empty($evidenceImage->evidence_image)) {
            $kpi['evidenceImage'] = $evidenceImage->evidence_image;
        }
        $kpi['percentageChartPieBreakdown'] = ($kpi['total_breakdown'] / $kpi['total_unit']) * 100;
        $kpi['percentageChartPieReady'] = ($kpi['total_unit'] - $kpi['total_breakdown']) / $kpi['total_unit'] * 100;
        $kpi['kpiVhmsAchievement'] = ($kpi['total_unit'] - $kpi['total_breakdown']) / $kpi['total_unit'] * 100;

        $bulan = $request->month;
        if ($bulan == 1) {
            $bulan = "Januari";
        } elseif ($bulan == 2) {
            $bulan = "Februari";
        } elseif ($bulan == 3) {
            $bulan = "Maret";
        } elseif ($bulan == 4) {
            $bulan = "April";
        } elseif ($bulan == 5) {
            $bulan = "Mei";
        } elseif ($bulan == 6) {
            $bulan = "Juni";
        } elseif ($bulan == 7) {
            $bulan = "Juli";
        } elseif ($bulan == 8) {
            $bulan = "Agustus";
        } elseif ($bulan == 9) {
            $bulan = "September";
        } elseif ($bulan == 10) {
            $bulan = "Oktober";
        } elseif ($bulan == 11) {
            $bulan = "November";
        } elseif ($bulan == 12) {
            $bulan = "Desember";
        }


        $returnDataKpi = [
            'periode' => $request->week_data . ' ' . $bulan . ' ' . $request->year,
            'total_unit' => $kpi['total_unit'],
            'vhms_unit_terdownload' => $kpi['total_ter_download'],
            'vhms_unit_breakdown' => $kpi['total_breakdown'],
            'percentageVhmsTerDownload' => $kpi['percentageChartPieReady'],
            'percentageVhmsBreakdown' => $kpi['percentageChartPieBreakdown'],
            'achhievement' => $kpi['kpiVhmsAchievement'],
            'evidence_image' => $kpi['evidenceImage'],
            'dataTableBreakdown' => $arrayDataKpiVhmsTableBreakdownList,
        ];
        return response()->json($returnDataKpi);
    }
}
