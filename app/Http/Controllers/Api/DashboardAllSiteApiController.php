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
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardAllSiteApiController extends Controller
{
    private const ALLOWED_ROLES = ['ict_ho', 'ict_developer'];

    private const COMPLAINT_CATEGORIES = [
        'TELKOMSEL', 'RADIO', 'SERVER', 'SS6', 'WEBSITE',
        'NETWORK', 'SAP', 'PC/NB', 'PRINTER', 'SOC', 'OTHER',
    ];

    public function __invoke(Request $request)
    {
        if (! in_array($request->user()?->role, self::ALLOWED_ROLES, true)) {
            abort(403, 'You do not have permission to access this resource.');
        }

        $monthRaw = $request->integer('month');
        $month = (int) ($monthRaw ?: Carbon::now()->month);
        $year = (int) ($request->integer('year') ?: Carbon::now()->year);
        $quarter = (int) ($request->integer('quarter') ?: Carbon::now()->quarter);

        $complaintsBase = Aduan::query();

        // Kalau month tidak dikirim, tampilkan year-to-date supaya data tidak kosong
        $complaintsMonth = Aduan::query()->whereYear('date_of_complaint', $year);
        if ($monthRaw) {
            $complaintsMonth->whereMonth('date_of_complaint', $month);
        }

        $totalMonth = (clone $complaintsMonth)->count();

        $categoryBreakdown = [];
        foreach (self::COMPLAINT_CATEGORIES as $category) {
            $count = (clone $complaintsMonth)->where('category_name', $category)->count();
            $categoryBreakdown[$category] = [
                'count' => $count,
                'percent' => $totalMonth > 0 ? round(($count / $totalMonth) * 100, 2) : 0,
            ];
        }

        $monthYearFilter = ['month' => $month, 'year' => $year];

        return response()->json([
            'data' => [
                'site_label' => 'All Site',
                'complaints' => [
                    'summary' => [
                        'open' => (clone $complaintsBase)->where('status', 'OPEN')->count(),
                        'progress' => (clone $complaintsBase)->where('status', 'PROGRESS')->count(),
                        'closed' => (clone $complaintsBase)->where('status', 'CLOSED')->count(),
                        'cancel' => (clone $complaintsBase)->where('status', 'CANCEL')->count(),
                        'categories' => $categoryBreakdown,
                    ],
                ],
                'inventory' => [
                    'device_monitoring' => [
                        'access_point' => $this->statusSummary(InvAp::query()),
                        'switch' => $this->statusSummary(InvSwitch::query()),
                        'wireless' => $this->statusSummary(InvWirelless::query()),
                        'printer' => $this->statusSummary(InvPrinter::query()),
                        'cctv' => $this->statusSummary(InvCctv::query()),
                        'komputer' => $this->statusSummary(InvComputer::query()),
                        'laptop' => $this->statusSummary(InvLaptop::query()),
                    ],
                ],
                'inspection' => [
                    'inspection_achievement' => [
                        'laptop' => $this->inspectionSummary(
                            InspeksiLaptop::query(),
                            ['year' => $year]
                        ),
                        'komputer' => $this->inspectionSummary(
                            InspeksiComputer::query(),
                            ['year' => $year, 'triwulan' => $quarter]
                        ),
                        'access_point' => $this->inspectionSummary(
                            InspeksiAccessPoint::query(),
                            $monthYearFilter
                        ),
                        'switch' => $this->inspectionSummary(
                            InspeksiSwitch::query(),
                            $monthYearFilter
                        ),
                        'wireless' => $this->inspectionSummary(
                            InspeksiWirelless::query(),
                            $monthYearFilter
                        ),
                        'printer' => $this->inspectionSummary(
                            InspeksiPrinter::query(),
                            $monthYearFilter
                        ),
                        'tower' => $this->inspectionSummary(
                            InspeksiTower::query(),
                            $monthYearFilter
                        ),
                        'mobile_tower' => $this->inspectionSummary(
                            InspeksiMobileTower::query(),
                            $monthYearFilter
                        ),
                        'panel_box_network' => $this->inspectionSummary(
                            InspeksiPanelBoxNetwork::query(),
                            $monthYearFilter
                        ),
                    ],
                ],
            ],
        ]);
    }

    private function statusSummary($query): array
    {
        return [
            'ready_used' => (clone $query)->where('status', 'READY_USED')->count(),
            'ready_standby' => (clone $query)->where('status', 'READY_STANDBY')->count(),
            'scrap' => (clone $query)->where('status', 'SCRAP')->count(),
            'breakdown' => (clone $query)->where('status', 'BREAKDOWN')->count(),
        ];
    }

    private function inspectionSummary($query, array $filters): array
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
