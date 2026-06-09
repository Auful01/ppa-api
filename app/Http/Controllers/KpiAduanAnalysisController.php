<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InvComputer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KpiAduanAnalysisController extends Controller
{
    public function index()
    {
        $auth = auth()->user();

        return Inertia::render(
            'Inventory/Kpi/KpiJobAduanAnalysis/KpiAduanAnalysisIndex',
            [
                'chartData' => [
                    'labels' => [],
                    'series' => [],
                ],
                'site' => $auth->site,
            ]
        );
    }

    public function getComplaintDetails(Request $request)
    {
        $complaints = Aduan::query()
            ->whereYear('date_of_complaint', $request->year)
            ->whereMonth('date_of_complaint', $request->month)
            ->where('site', $request->site)
            ->where('category_name', $request->category)
            ->orderBy('complaint_code', 'desc')
            ->get();

        return response()->json([
                'complaints' => $complaints,
        ]);
    }

    public function complaintPerMonth(Request $request)
    {
        $user = Auth::user();
        $site = $user->site;
        // $year = now()->year;
        $currentMonth = now()->month;

        $categories = Cache::remember("root_cause_{$site}", 3600, function () use ($site) {
            return DB::table('root_cause_categories')
                ->where('site_type', $site === 'HO' ? 'HO' : 'SITE')
                ->pluck('category_root_cause');
        });

        if ($categories->isEmpty()) {
            return response()->json([
                'labels' => [],
                'data' => [],
            ]);
        }

        $startDate = Carbon::create($request->year, 1, 1)->startOfDay();
        $endDate = Carbon::create($request->year, $currentMonth, 1)->endOfMonth()->endOfDay();

        $complaints = DB::table('aduans')
            ->whereBetween('date_of_complaint', [$startDate, $endDate])
            ->where('site', $site)
            ->whereIn('category_name', $categories)
            ->whereNull('deleted_at') // ← abaikan soft deleted
            ->selectRaw('MONTH(date_of_complaint) as month, category_name, COUNT(*) as total')
            ->groupBy('month', 'category_name')
            ->get();

        // Inisialisasi array
        $result = [];
        foreach ($categories as $category) {
            $result[$category] = array_fill(1, $currentMonth, 0);
        }

        foreach ($complaints as $row) {
            if (isset($result[$row->category_name])) {
                $result[$row->category_name][$row->month] = $row->total;
            }
        }

        $labels = [];
        for ($m = 1; $m <= $currentMonth; $m++) {
            $labels[] = strtoupper(Carbon::createFromDate($request->year, $m, 1)->isoFormat('MMM'));
        }

        // Ubah data ke format Highcharts
        $series = [];
        foreach ($result as $category => $values) {
            $series[] = [
                'name' => $category,
                'data' => array_values($values),
            ];
        }

        return response()->json([
            'chartData' => [
                'labels' => $labels,
                'series' => $series,
            ],
            'site' => $site,
        ]);
    }
}
