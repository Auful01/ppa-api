<?php

namespace App\Http\Controllers;

use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Models\InvComputer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class KpiInspeksiController extends Controller
{
    public function index()
    {
        $auth = auth()->user();

        return Inertia::render(
            'Inventory/Kpi/KpiInspeksi/KpiInspeksiIndex',
            [
                'chartData' => [
                    'labels' => [],
                    'sudah_inspeksi' => [],
                    'belum_inspeksi' => [],
                ],
                'site' => $auth->site,
            ]
        );
    }

    public function countKpi(Request $request)
    {
        $auth = auth()->user();
        $device = strtoupper($request->device);
        $year = $request->year ? (int)$request->year : Carbon::now()->year;
        $quarter = $request->quarter;

        $chartData = [
            'labels' => [],
            'sudah_inspeksi' => [],
            'belum_inspeksi' => [],
        ];

        if ($device === 'LAPTOP') {
            $tahunSekarang = Carbon::now()->year;

            if ($request->year) {
                $tahunList = [(int)$request->year];
            } else {
                $tahunMulai = max(2025, $tahunSekarang - 4);
                $tahunList = range($tahunMulai, $tahunSekarang);
            }

            foreach ($tahunList as $th) {
                $countAll = InspeksiLaptop::where('site', $auth->site)->where('year', $th)->count();
                $countY = InspeksiLaptop::where('site', $auth->site)->where('year', $th)->where('inspection_status', 'Y')->count();
                $countN = InspeksiLaptop::where('site', $auth->site)->where('year', $th)->where('inspection_status', 'N')->count();

                $percentY = $countAll ? ($countY / $countAll) * 100 : 0;
                $percentN = $countAll ? ($countN / $countAll) * 100 : 0;

                $chartData['labels'][] = $th;
                $chartData['sudah_inspeksi'][] = round($percentY, 2);
                $chartData['belum_inspeksi'][] = round($percentN, 2);
            }
        } elseif ($device === 'COMPUTER') {
            // dd($request);
            if ($request->quarter && $request->year) {
                // Hanya 1 quarter & 1 tahun yang diminta
                $quarter = (int) $request->quarter;


                $countAll = InspeksiComputer::where('site', $auth->site)
                    ->where('year', $year)
                    ->where('triwulan', $quarter)
                    ->count();

                $countY = InspeksiComputer::where('site', $auth->site)
                    ->where('year', $year)
                    ->where('triwulan', $quarter)
                    ->where('inspection_status', 'Y')
                    ->count();

                $countN = InspeksiComputer::where('site', $auth->site)
                    ->where('year', $year)
                    ->where('triwulan', $quarter)
                    ->where('inspection_status', 'N')
                    ->count();
                $chartData['labels'][] = "Q{$quarter}";
                $chartData['sudah_inspeksi'][] = $countAll ? round(($countY / $countAll) * 100, 2) : 0;
                $chartData['belum_inspeksi'][] = $countAll ? round(($countN / $countAll) * 100, 2) : 0;
            } elseif ($request->month && $request->year) {
                $month = $request->month ? (int) $request->month : Carbon::now()->month;
                // return response()->json($month);
                $countAll = InspeksiComputer::where('site', $auth->site)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->count();

                $countY = InspeksiComputer::where('site', $auth->site)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('inspection_status', 'Y')
                    ->count();

                $countN = InspeksiComputer::where('site', $auth->site)
                    ->where('year', $year)
                    ->where('month', $month)
                    ->where('inspection_status', 'N')
                    ->count();

                $monthName = Carbon::create()->month($month)->translatedFormat('F');

                $chartData['labels'][] = $monthName;
                $chartData['sudah_inspeksi'][] = $countAll ? round(($countY / $countAll) * 100, 2) : 0;
                $chartData['belum_inspeksi'][] = $countAll ? round(($countN / $countAll) * 100, 2) : 0;
            } else {
                // Tidak ada input quarter -> tampilkan Q1-Q4 di tahun yg diminta/now
                $selectedYear = $request->year ? (int) $request->year : Carbon::now()->year;

                if ($auth->site === 'ADW') {
                    foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12] as $mnt) {
                        $countAll = InspeksiComputer::where('site', $auth->site)
                            ->where('year', $selectedYear)
                            ->where('month', $mnt)
                            ->count();

                        $countY = InspeksiComputer::where('site', $auth->site)
                            ->where('year', $selectedYear)
                            ->where('month', $mnt)
                            ->where('inspection_status', 'Y')
                            ->count();

                        $countN = InspeksiComputer::where('site', $auth->site)
                            ->where('year', $selectedYear)
                            ->where('month', $mnt)
                            ->where('inspection_status', 'N')
                            ->count();

                        $monthName = Carbon::create()->month($mnt)->translatedFormat('F');

                        $chartData['labels'][] = $monthName;
                        $chartData['sudah_inspeksi'][] = $countAll ? round(($countY / $countAll) * 100, 2) : 0;
                        $chartData['belum_inspeksi'][] = $countAll ? round(($countN / $countAll) * 100, 2) : 0;
                    }
                } else {
                    foreach ([1, 2, 3, 4] as $qtr) {
                        $countAll = InspeksiComputer::where('site', $auth->site)
                            ->where('year', $selectedYear)
                            ->where('triwulan', $qtr)
                            ->count();

                        $countY = InspeksiComputer::where('site', $auth->site)
                            ->where('year', $selectedYear)
                            ->where('triwulan', $qtr)
                            ->where('inspection_status', 'Y')
                            ->count();

                        $countN = InspeksiComputer::where('site', $auth->site)
                            ->where('year', $selectedYear)
                            ->where('triwulan', $qtr)
                            ->where('inspection_status', 'N')
                            ->count();

                        $chartData['labels'][] = "Q{$qtr}";
                        $chartData['sudah_inspeksi'][] = $countAll ? round(($countY / $countAll) * 100, 2) : 0;
                        $chartData['belum_inspeksi'][] = $countAll ? round(($countN / $countAll) * 100, 2) : 0;
                    }
                }
            }
        } elseif ($device === 'PRINTER') {

            // Jika month & year ada → pakai request
            // Jika kosong → pakai bulan & tahun sekarang
            $month = $request->month
                ? (int) $request->month
                : Carbon::now()->month;

            $year = $request->year
                ? (int) $request->year
                : Carbon::now()->year;

            $countAll = InspeksiPrinter::where('site', $auth->site)
                ->where('year', $year)
                ->where('month', $month)
                ->count();

            $countY = InspeksiPrinter::where('site', $auth->site)
                ->where('year', $year)
                ->where('month', $month)
                ->where('inspection_status', 'Y')
                ->count();

            $countN = InspeksiPrinter::where('site', $auth->site)
                ->where('year', $year)
                ->where('month', $month)
                ->where('inspection_status', 'N')
                ->count();

            // Nama bulan (Indonesia)
            $monthName = Carbon::create()
                ->month($month)
                ->translatedFormat('F');

            $chartData['labels'][] = "{$monthName} {$year}";
            $chartData['sudah_inspeksi'][] = $countAll
                ? round(($countY / $countAll) * 100, 2)
                : 0;

            $chartData['belum_inspeksi'][] = $countAll
                ? round(($countN / $countAll) * 100, 2)
                : 0;
        } elseif ($device === 'MOBILE TOWER') {

            // Jika month & year ada → pakai request
            // Jika kosong → pakai bulan & tahun sekarang
            $month = $request->month
                ? (int) $request->month
                : Carbon::now()->month;

            $year = $request->year
                ? (int) $request->year
                : Carbon::now()->year;

            $countAll = InspeksiMobileTower::where('site', $auth->site)
                ->where('year', $year)
                ->where('month', $month)
                ->count();

            $countY = InspeksiMobileTower::where('site', $auth->site)
                ->where('year', $year)
                ->where('month', $month)
                ->where('inspection_status', 'Y')
                ->count();

            $countN = InspeksiMobileTower::where('site', $auth->site)
                ->where('year', $year)
                ->where('month', $month)
                ->where('inspection_status', 'N')
                ->count();

            // Nama bulan (Indonesia)
            $monthName = Carbon::create()
                ->month($month)
                ->translatedFormat('F');

            $chartData['labels'][] = "{$monthName} {$year}";
            $chartData['sudah_inspeksi'][] = $countAll
                ? round(($countY / $countAll) * 100, 2)
                : 0;

            $chartData['belum_inspeksi'][] = $countAll
                ? round(($countN / $countAll) * 100, 2)
                : 0;
        } 


        // dd($chartData);

        return response()->json([
            'chartData' => [
                'labels' => $chartData['labels'],
                'sudah_inspeksi' => $chartData['sudah_inspeksi'],
                'belum_inspeksi' => $chartData['belum_inspeksi'],
            ],
            'site' => $auth->site,
        ]);
    }
}
