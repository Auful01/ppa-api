<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class KpiAduanAnalysisApiController extends Controller
{
    public function chart(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000'],
            'site' => ['nullable', 'string'],
        ]);

        $site = SiteContext::resolve($request);
        $currentMonth = now()->month;

        $categories = Cache::remember("root_cause_{$site}", 3600, function () use ($site) {
            return DB::table('root_cause_categories')
                ->where('site_type', $site === 'HO' ? 'HO' : 'SITE')
                ->pluck('category_root_cause');
        });

        if ($categories->isEmpty()) {
            return response()->json([
                'chartData' => ['labels' => [], 'series' => []],
                'site' => $site,
            ]);
        }

        $startDate = Carbon::create($validated['year'], 1, 1)->startOfDay();
        $endDate = Carbon::create($validated['year'], $currentMonth, 1)->endOfMonth()->endOfDay();

        $complaints = DB::table('aduans')
            ->whereBetween('date_of_complaint', [$startDate, $endDate])
            ->where('site', $site)
            ->whereIn('category_name', $categories)
            ->whereNull('deleted_at')
            ->selectRaw('MONTH(date_of_complaint) as month, category_name, COUNT(*) as total')
            ->groupBy('month', 'category_name')
            ->get();

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
            $labels[] = strtoupper(Carbon::createFromDate($validated['year'], $m, 1)->isoFormat('MMM'));
        }

        $series = [];
        foreach ($result as $category => $values) {
            $series[] = ['name' => $category, 'data' => array_values($values)];
        }

        return response()->json([
            'chartData' => ['labels' => $labels, 'series' => $series],
            'site' => $site,
        ]);
    }

    public function details(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000'],
            'month' => ['required', 'integer', 'between:1,12'],
            'site' => ['required', 'string'],
            'category' => ['required', 'string'],
        ]);

        $site = SiteContext::resolve($request);

        $complaints = Aduan::query()
            ->whereYear('date_of_complaint', $validated['year'])
            ->whereMonth('date_of_complaint', $validated['month'])
            ->where('site', $site)
            ->where('category_name', $validated['category'])
            ->orderByDesc('complaint_code')
            ->get();

        return response()->json([
            'complaints' => $complaints,
        ]);
    }
}
