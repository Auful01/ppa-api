<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aduan;
use App\Models\InspeksiAccessPoint;
use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPanelBoxNetwork;
use App\Models\InspeksiPrinter;
use App\Models\InspeksiSwitch;
use App\Models\InspeksiTower;
use App\Models\InspeksiWirelless;
use App\Models\InvAp;
use App\Models\InvCctv;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\InvPrinter;
use App\Models\InvSwitch;
use App\Models\InvWirelless;
use App\Support\Api\SiteContext;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function __invoke(Request $request)
    {
        $site = SiteContext::resolve($request);
        $month = (int) ($request->integer('month') ?: Carbon::now()->month);
        $year = (int) ($request->integer('year') ?: Carbon::now()->year);
        $quarter = (int) ($request->integer('quarter') ?: Carbon::now()->quarter);

        $complaints = Aduan::query()->orderByDesc('date_of_complaint');
        SiteContext::apply($complaints, 'site', $site);

        $inventoryCards = [
            'access_point' => $this->statusSummary(InvAp::query(), $site),
            'switch' => $this->statusSummary(InvSwitch::query(), $site),
            'wireless' => $this->statusSummary(InvWirelless::query(), $site),
            'printer' => $this->statusSummary(InvPrinter::query(), $site),
            'cctv' => $this->statusSummary(InvCctv::query(), $site),
            'computer' => $this->statusSummary(InvComputer::query(), $site),
            'laptop' => $this->statusSummary(InvLaptop::query(), $site),
        ];

        $aduanMonthQuery = Aduan::query()->whereMonth('created_at', $month)->whereYear('created_at', $year);
        SiteContext::apply($aduanMonthQuery, 'site', $site);
        $totalAduan = (clone $aduanMonthQuery)->count();

        $categories = ['TELKOMSEL', 'RADIO', 'SERVER', 'SS6', 'WEBSITE', 'NETWORK', 'SAP', 'PC/NB', 'PRINTER', 'SOC', 'OTHER'];
        $complaintBreakdown = [];
        foreach ($categories as $category) {
            $count = (clone $aduanMonthQuery)->where('category_name', $category)->count();
            $complaintBreakdown[$category] = [
                'count' => $count,
                'percent' => $totalAduan > 0 ? round(($count / $totalAduan) * 100, 2) : 0,
            ];
        }

        $isHo = SiteContext::isHo($site);
        $monthYearFilter = ['month' => $month, 'year' => $year];

        if ($isHo) {
            $inspectionAchievement = [
                'laptop' => $this->inspectionSummary(InspeksiLaptop::query(), $site, ['year' => $year]),
                'komputer' => $this->inspectionSummary(InspeksiComputer::query(), $site, ['year' => $year, 'triwulan' => $quarter]),
            ];
        } else {
            $inspectionAchievement = [
                'access_point' => $this->inspectionSummaryViaInventory(
                    InspeksiAccessPoint::query(), 'inv_ap_id', InvAp::query(), $site, $monthYearFilter
                ),
                'switch' => $this->inspectionSummaryViaInventory(
                    InspeksiSwitch::query(), 'inv_switch_id', InvSwitch::query(), $site, $monthYearFilter
                ),
                'wireless' => $this->inspectionSummaryViaInventory(
                    InspeksiWirelless::query(), 'inv_wirelless_id', InvWirelless::query(), $site, $monthYearFilter
                ),
                'printer' => $this->inspectionSummary(InspeksiPrinter::query(), $site, $monthYearFilter),
                'mobile_tower' => $this->inspectionSummary(InspeksiMobileTower::query(), $site, $monthYearFilter),
                // Tower & panel box tidak punya kolom site di inventory, ditampilkan tanpa filter site
                'tower' => $this->inspectionSummaryNoSite(InspeksiTower::query(), $monthYearFilter),
                'panel_box_network' => $this->inspectionSummaryNoSite(InspeksiPanelBoxNetwork::query(), $monthYearFilter),
            ];
        }

        return response()->json([
            'site' => $site,
            'complaints' => [
                'latest' => $complaints->limit(20)->get(),
                'summary' => [
                    'open' => (clone $complaints)->where('status', 'OPEN')->count(),
                    'closed' => (clone $complaints)->where('status', 'CLOSED')->count(),
                    'progress' => (clone $complaints)->where('status', 'PROGRESS')->count(),
                    'cancel' => (clone $complaints)->where('status', 'CANCEL')->count(),
                    'total_month' => $totalAduan,
                    'categories' => $complaintBreakdown,
                ],
            ],
            'inventory' => $inventoryCards,
            'inspection' => $inspectionAchievement,
        ]);
    }

    private function statusSummary($query, ?string $site): array
    {
        SiteContext::apply($query, 'site', $site);

        return [
            'ready_used' => (clone $query)->where('status', 'READY_USED')->count(),
            'ready_standby' => (clone $query)->where('status', 'READY_STANDBY')->count(),
            'breakdown' => (clone $query)->where('status', 'BREAKDOWN')->count(),
            'scrap' => (clone $query)->where('status', 'SCRAP')->count(),
        ];
    }

    private function inspectionSummary($query, ?string $site, array $filters): array
    {
        SiteContext::apply($query, 'site', $site);

        foreach ($filters as $column => $value) {
            $query->where($column, $value);
        }

        $total = (clone $query)->count();
        $done = (clone $query)->where('inspection_status', 'Y')->count();
        $pending = (clone $query)->where('inspection_status', 'N')->count();

        return [
            'total' => $total,
            'done' => $done,
            'pending' => $pending,
            'done_percent' => $total > 0 ? round(($done / $total) * 100, 2) : 0,
            'pending_percent' => $total > 0 ? round(($pending / $total) * 100, 2) : 0,
        ];
    }

    // Untuk model inspeksi yang tidak punya kolom site langsung (AP, Switch, Wireless)
    private function inspectionSummaryViaInventory($inspQuery, string $fk, $invQuery, ?string $site, array $filters): array
    {
        $ids = SiteContext::apply($invQuery, 'site', $site)->pluck('id');
        $inspQuery->whereIn($fk, $ids);

        foreach ($filters as $column => $value) {
            $inspQuery->where($column, $value);
        }

        $total = (clone $inspQuery)->count();
        $done = (clone $inspQuery)->where('inspection_status', 'Y')->count();
        $pending = (clone $inspQuery)->where('inspection_status', 'N')->count();

        return [
            'total' => $total,
            'done' => $done,
            'pending' => $pending,
            'done_percent' => $total > 0 ? round(($done / $total) * 100, 2) : 0,
            'pending_percent' => $total > 0 ? round(($pending / $total) * 100, 2) : 0,
        ];
    }

    // Untuk model inspeksi yang inventory-nya tidak punya kolom site (Tower, PanelBox)
    private function inspectionSummaryNoSite($query, array $filters): array
    {
        foreach ($filters as $column => $value) {
            $query->where($column, $value);
        }

        $total = (clone $query)->count();
        $done = (clone $query)->where('inspection_status', 'Y')->count();
        $pending = (clone $query)->where('inspection_status', 'N')->count();

        return [
            'total' => $total,
            'done' => $done,
            'pending' => $pending,
            'done_percent' => $total > 0 ? round(($done / $total) * 100, 2) : 0,
            'pending_percent' => $total > 0 ? round(($pending / $total) * 100, 2) : 0,
        ];
    }
}
