<?php

namespace App\Http\Controllers;

use App\Models\DispatchVhms;
use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Models\kpiVhms;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ChartInspeksiValeController extends Controller
{
    public function index()
    {
        $auth = auth()->user();

        $site = 'VIB';

        return Inertia::render(
            'Inspeksi/SiteVale/ChartInspeksiIndex',
            [
                'chartData' => [
                    'labelsLaptop' => [],
                    'persenLaptop' => [],
                    'persenKomputer' => [],
                    'persenPrinter' => [],
                    'persenMT' => [],
                ],
                'site' => $site,
            ]
        );
    }

    public function countKpi(Request $request)
    {
        $auth = auth()->user();
        $site = 'VIB';
        $year = $request->year ? (int) $request->year : Carbon::now()->year;

        $chartData = [
            'labelsLaptop' => [],
            'persenLaptop' => [],
            'persenKomputer' => [],
            'persenPrinter' => [],
            'persenMT' => [],
        ];

        $chartData['labelsLaptop'][] = $year;

        $countAllDataInspeksiLaptop = InspeksiLaptop::where('site', $site)
            ->where('year', $year)
            ->count();
        $countSudahInspeksiLaptop = InspeksiLaptop::where('inspection_status', 'Y')
            ->where('site', $site)
            ->where('year', $year)
            ->count();
        if ($countAllDataInspeksiLaptop > 0) {
            $percentLaptopSudahInspeksi = ($countSudahInspeksiLaptop / $countAllDataInspeksiLaptop) * 100;
        } else {
            $percentLaptopSudahInspeksi = 0;
        }

        $chartData['persenLaptop'][] = round($percentLaptopSudahInspeksi, 2);



        for ($i = 1; $i <= 4; $i++) {
            $countAllDataInspeksiComputer = InspeksiComputer::where('site', $site)
                ->where('triwulan', $i)
                ->where('year', $year)
                ->count();
            $countSudahInspeksiComputer = InspeksiComputer::where('inspection_status', 'Y')
                ->where('site', $site)
                ->where('triwulan', $i)
                ->where('year', $year)
                ->count();

            if ($countAllDataInspeksiComputer > 0) {
                $percentComputerSudahInspeksi = ($countSudahInspeksiComputer / $countAllDataInspeksiComputer) * 100;
            } else {
                $percentComputerSudahInspeksi = 0;
            }

            $chartData['persenKomputer'][] = round($percentComputerSudahInspeksi, 2);
        }


        for ($i = 1; $i <= 12; $i++) {
            $countAllDataInspeksiPrinter = InspeksiPrinter::where('site', $site)
                ->where('year', $year)
                ->where('month', $i)
                ->count();
            $countSudahInspeksiPrinter = InspeksiPrinter::where('inspection_status', 'Y')
                ->where('site', $site)
                ->where('month', $i)
                ->where('year', $year)
                ->count();
            if ($countAllDataInspeksiPrinter > 0) {
                $percentPrinterSudahInspeksi = ($countSudahInspeksiPrinter / $countAllDataInspeksiPrinter) * 100;
            } else {
                $percentPrinterSudahInspeksi = 0;
            }

            $chartData['persenPrinter'][] = round($percentPrinterSudahInspeksi, 2);
        }

        for ($i = 1; $i <= 12; $i++) {
            $countAllDataInspeksiMT = InspeksiMobileTower::where('site', $site)
                ->where('year', $year)
                ->where('month', $i)
                ->count();
            $countSudahInspeksiMT = InspeksiMobileTower::where('inspection_status', 'Y')
                ->where('site', $site)
                ->where('month', $i)
                ->where('year', $year)
                ->count();
            if ($countAllDataInspeksiMT > 0) {
                $percentMTSudahInspeksi = ($countSudahInspeksiMT / $countAllDataInspeksiMT) * 100;
            } else {
                $percentMTSudahInspeksi = 0;
            }

            $chartData['persenMT'][] = round($percentMTSudahInspeksi, 2);
        }

        // dd($chartData);

        return response()->json([
            'chartData' => [
                'labelsLaptop' => $chartData['labelsLaptop'],
                'persenLaptop' => $chartData['persenLaptop'],
                'persenKomputer' => $chartData['persenKomputer'],
                'persenPrinter' => $chartData['persenPrinter'],
                'persenMT' => $chartData['persenMT'],
            ],
            'site' => $site,
        ]);
    }
}
