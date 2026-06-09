<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class PerangkatBreakdownController extends Controller
{

   public function index()
   {
      $auth = auth()->user();

      return Inertia::render(
         'Inventory/Kpi/KpiPerangkatBreakdown/KpiPerangkatBreakdownIndex',
         [
            'site' => $auth->site,
         ]
      );
   }
   // public function calculatePerformance(Request $request)
   // {
   //    $site = auth()->user()->site; // site user login

   //    // Jika tidak ada input tanggal, ambil awal bulan sampai hari ini
   //    $startDate = $request->get('start_date')
   //       ? Carbon::parse($request->get('start_date'))->startOfDay()
   //       : Carbon::now()->startOfMonth();

   //    $endDate = $request->get('end_date')
   //       ? Carbon::parse($request->get('end_date'))->endOfDay()
   //       : Carbon::now()->endOfDay();

   //    // daftar root cause yang ingin dihitung
   //    $rootCauseList = ['RAM', 'MONITOR', 'KABEL', 'OS', 'DRIVER', 'HARDISK', 'SOFTWARE', 'LAIN-LAIN'];

   //    // Ambil unit berdasarkan site user login
   //    $units = DB::table('inv_laptops')
   //       ->select('laptop_code')
   //       ->where('site', $site)
   //       ->groupBy('laptop_code')
   //       ->get();

   //    $unitCount = $units->count(); // jumlah perangkat unik
   //    $daysRange = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1;

   //    // Hitung total target time (24 jam x hari x unit)
   //    $totalTargetTime = 24 * $daysRange * $unitCount;

   //    // Ambil data breakdown untuk site terkait
   //    $breakdowns = DB::table('perangkat_breakdowns')
   //       ->where('site', $site)
   //       ->whereBetween('created_date', [$startDate, $endDate])
   //       ->get();

   //    $totalBreakdownTime = 0;
   //    $breakdownDevices = [];

   //    foreach ($breakdowns as $bd) {
   //       $start = Carbon::parse($bd->start_time);
   //       $end = $bd->end_time ? Carbon::parse($bd->end_time) : Carbon::now();

   //       // Hitung total jam dan pastikan hasil positif
   //       $hours = abs($end->diffInMinutes($start) / 60);

   //       $totalBreakdownTime += $hours;
   //       $breakdownDevices[$bd->inventory_number] = true;
   //    }

   //    $totalBreakdownDevices = count($breakdownDevices);
   //    $totalRunningTime = $totalTargetTime - $totalBreakdownTime;

   //    // // Format angka sesuai format Indonesia (2 desimal, koma, titik)
   //    // $fmt = fn ($num) => number_format($num, 2, ',', '.');

   //    // return response()->json([
   //    //    'site' => $site,
   //    //    'periode' => [
   //    //       'start' => $startDate->toDateString(),
   //    //       'end' => $endDate->toDateString(),
   //    //    ],
   //    //    'jumlah_perangkat' => $unitCount,
   //    //    'total_target_time_jam' => $fmt($totalTargetTime),
   //    //    'total_breakdown_time_jam' => $fmt(abs($totalBreakdownTime)),
   //    //    'total_running_time_jam' => $fmt($totalRunningTime > 0 ? $totalRunningTime : 0),
   //    //    'total_breakdown_perangkat' => $totalBreakdownDevices,
   //    // ]);


   //    // Hitung persentase breakdown vs normal
   //    $persenBreakdown = $unitCount > 0 ? round(($totalBreakdownDevices / $unitCount) * 100, 2) : 0;
   //    $persenReady = 100 - $persenBreakdown;

   //    // hitung total root cause hanya untuk kategori PC/NB
   //    $rootCauseData = DB::table('perangkat_breakdowns')
   //       ->select('root_cause', DB::raw('COUNT(*) as total'))
   //       ->where('site', $site)
   //       ->whereBetween('created_date', [$startDate, $endDate])
   //       ->whereIn('root_cause', $rootCauseList)
   //       ->groupBy('root_cause')
   //       ->pluck('total', 'root_cause')
   //       ->toArray();

   //    // pastikan semua root cause tampil meski 0
   //    $rootCauseCount = [];
   //    foreach ($rootCauseList as $rc) {
   //       $rootCauseCount[$rc] = $rootCauseData[$rc] ?? 0;
   //    }

   //    // format angka lokal
   //    $fmt = fn ($num) => number_format($num, 2, ',', '.');

   //    return response()->json([
   //       'site' => $site,
   //       'periode' => [
   //          'start' => $startDate->toDateString(),
   //          'end' => $endDate->toDateString(),
   //       ],
   //       'jumlah_perangkat' => $unitCount,
   //       'total_target_time_jam' => $fmt($totalTargetTime),
   //       'total_breakdown_time_jam' => $fmt(abs($totalBreakdownTime)),
   //       'total_running_time_jam' => $fmt($totalRunningTime > 0 ? $totalRunningTime : 0),
   //       'total_breakdown_perangkat' => $totalBreakdownDevices,

   //       // data untuk highchart (pie breakdown vs normal)
   //       'highchart' => [
   //          ['name' => 'Breakdown', 'y' => $persenBreakdown],
   //          ['name' => 'Ready', 'y' => $persenReady],
   //       ],

   //       'persentase' => [
   //          'breakdown' => $persenBreakdown,
   //          'ready' => $persenReady,
   //       ],

   //       // ✅ tambahan: total root cause hanya untuk PC/NB
   //       'root_cause_count' => $rootCauseCount
   //    ]);
   // }

   public function calculatePerformance(Request $request)
   {
      $site = auth()->user()->site;
      $deviceCategory = strtoupper($request->get('device_category', 'PC/NB')); // default PC/NB

      // Tentukan tabel dan kolom kunci sesuai jenis perangkat
      $deviceTables = [
         'PC/NB'     => ['table' => 'inv_laptops',   'code_field' => 'laptop_code'],
         'PRINTER'   => ['table' => 'inv_printers',  'code_field' => 'printer_code'],
         'SCANNER'   => ['table' => 'inv_scanners',  'code_field' => 'scanner_code'],
         // bisa tambah jenis lain nanti
      ];

      // Pastikan kategori valid
      if (!isset($deviceTables[$deviceCategory])) {
         return response()->json(['error' => "Device category '$deviceCategory' tidak dikenal."], 400);
      }

      $deviceTable = $deviceTables[$deviceCategory]['table'];
      $deviceCodeField = $deviceTables[$deviceCategory]['code_field'];

      // $bulan = $request->get('bulan');
      // $tahun = $request->get('tahun');

      $bulan = 12;
      $tahun = 2025;

      $namaBulan = [
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

      if ($bulan && $tahun) {
         // Input bulan valid (1–12)
         $bulan = (int) $bulan;
         $tahun = (int) $tahun;

         $startDate = Carbon::create($tahun, $bulan, 1)->startOfDay();
         $endDate   = Carbon::create($tahun, $bulan, 1)->endOfMonth()->endOfDay();
      } else {
         // fallback jika tidak ada input bulan dan tahun
         $startDate = Carbon::now()->startOfMonth();
         $endDate   = Carbon::now()->endOfMonth();
      }

      // daftar root cause untuk kategori PC/NB (bisa ditambah sesuai kategori lain)
      $rootCauseList = [
         'PC/NB'   => ['RAM', 'MONITOR', 'KABEL', 'OS', 'DRIVER', 'HARDISK', 'SOFTWARE', 'LAIN-LAIN'],
         'PRINTER' => ['KABEL', 'CARTIDGE', 'DRIVER', 'PAPER JAM', 'LAIN-LAIN'],
         'SCANNER' => ['KABEL', 'SENSOR', 'DRIVER', 'LAIN-LAIN'],
      ][$deviceCategory];

      // Ambil unit berdasarkan site dan jenis perangkat
      $units = DB::table($deviceTable)
         ->select($deviceCodeField)
         ->where('site', $site)
         ->groupBy($deviceCodeField)
         ->get();

      $unitCount = $units->count();
      $daysRange = round($startDate->diffInDays($endDate));


      // Hitung total target time (24 jam x hari x unit)
      $totalTargetTime = 24 * $daysRange * $unitCount;

      // Ambil data breakdown untuk site dan jenis perangkat
      $breakdowns = DB::table('perangkat_breakdowns')
         ->where('site', $site)
         ->where('device_category', $deviceCategory)
         ->whereBetween('created_date', [$startDate, $endDate])
         ->get();

      $totalBreakdownTime = 0;
      $breakdownDevices = [];

      foreach ($breakdowns as $bd) {
         $start = Carbon::parse($bd->start_time);
         $end = $bd->end_time ? Carbon::parse($bd->end_time) : Carbon::now();

         $hours = abs($end->diffInMinutes($start) / 60);
         $totalBreakdownTime += $hours;
         $breakdownDevices[$bd->inventory_number] = true;
      }

      $totalBreakdownDevices = count($breakdownDevices);
      $totalRunningTime = $totalTargetTime - $totalBreakdownTime;

      // Hitung persentase breakdown vs normal
      $persenBreakdown = $unitCount > 0 ? round(($totalBreakdownDevices / $unitCount) * 100, 2) : 0;
      $persenReady = 100 - $persenBreakdown;

      // Hitung total root cause hanya untuk kategori perangkat ini
      $rootCauseData = DB::table('perangkat_breakdowns')
         ->select('root_cause', DB::raw('COUNT(*) as total'))
         ->where('site', $site)
         ->where('device_category', $deviceCategory)
         ->whereBetween('created_date', [$startDate, $endDate])
         ->whereIn('root_cause', $rootCauseList)
         ->groupBy('root_cause')
         ->pluck('total', 'root_cause')
         ->toArray();

      // pastikan semua root cause muncul meski 0
      $rootCauseCount = [];
      foreach ($rootCauseList as $rc) {
         $rootCauseCount[$rc] = $rootCauseData[$rc] ?? 0;
      }

      $fmt = fn ($num) => number_format($num, 2, ',', '.');


      $breakdownTimePerUnit = [];
      foreach ($breakdowns as $bd) {
         $start = $bd->start_time ? Carbon::parse($bd->start_time) : null;
         $end = $bd->end_time ? Carbon::parse($bd->end_time) : Carbon::now();
         $hours = $start ? abs($end->diffInMinutes($start) / 60) : 0;

         $inv = $bd->inventory_number;
         if (!isset($breakdownTimePerUnit[$inv])) $breakdownTimePerUnit[$inv] = 0;
         $breakdownTimePerUnit[$inv] += $hours;
      }

      // Target per unit (jam) = 24 * daysRange (ubah jika target per unit berbeda)
      $targetPerUnit = 24 * $daysRange;

      $breakdownDetails = DB::table('perangkat_breakdowns as pb')
         ->join($deviceTable . ' as inv', 'pb.id_perangkat', '=', 'inv.id')
         ->leftJoin('user_alls as u', 'inv.user_alls_id', '=', 'u.id')
         ->select(
            DB::raw('pb.*, inv.*, u.id as user_id, u.nrp as user_nrp, u.username as user_name, u.department as user_department, u.position as user_position, u.email as user_email')
         )
         ->where('pb.site', $site)
         ->where('pb.device_category', $deviceCategory)
         ->whereBetween('pb.created_date', [$startDate, $endDate])
         ->orderByDesc('pb.start_time')
         ->get();

      // Enrich setiap record dengan durasi record, total per unit, target per unit, running time per unit, percentage
      $breakdownDetailsEnriched = $breakdownDetails->map(function ($row) use ($targetPerUnit, $breakdownTimePerUnit) {

         $start = $row->start_time ? Carbon::parse($row->start_time) : null;
         $end = $row->end_time ? Carbon::parse($row->end_time) : Carbon::now();

         // Durasi breakdown record ini
         $bd_time_record = $start ? abs($end->diffInMinutes($start) / 60) : 0;

         // Total breakdown per unit
         $inv = $row->inventory_number;
         $bd_time_unit_total = isset($breakdownTimePerUnit[$inv]) ? $breakdownTimePerUnit[$inv] : 0;

         // Running time unit
         $running_time_unit = $targetPerUnit - $bd_time_unit_total;
         if ($running_time_unit < 0) $running_time_unit = 0;

         // Percentage running
         $percentage_unit_running = $targetPerUnit > 0
            ? round(($running_time_unit / $targetPerUnit) * 100, 2)
            : 0;

         // format helper
         $fmt2 = fn ($n) => number_format($n, 2, ',', '.');

         return (object) array_merge((array) $row, [
            'bd_time_record_hours' => $bd_time_record,
            'bd_time_unit_total_hours' => $bd_time_unit_total,
            'target_time_unit_hours' => $targetPerUnit,
            'running_time_unit_hours' => $running_time_unit,

            // persentase unit running
            'percentage_unit_running' => $percentage_unit_running,
            'percentage_unit_running_fmt' => $fmt2($percentage_unit_running) . " %",

            // existing fields
            'bd_time_record_hours_fmt' => $fmt2($bd_time_record),
            'bd_time_unit_total_hours_fmt' => $fmt2($bd_time_unit_total),
            'target_time_unit_hours_fmt' => $fmt2($targetPerUnit),
            'running_time_unit_hours_fmt' => $fmt2($running_time_unit),
         ]);
      });


      return response()->json([
         'site' => $site,
         'device_category' => $deviceCategory,
         'periode' => [
            // 'start' => $startDate->toDateString(),
            // 'end' => $endDate->toDateString(),
            'start' => $startDate->format('d') . ' ' . $namaBulan[$bulan] . ' ' . $tahun,
            'end'   => $endDate->format('d') . ' ' . $namaBulan[$bulan] . ' ' . $tahun,
         ],
         'jumlah_perangkat' => $unitCount,
         'total_target_time_jam' => $fmt($totalTargetTime),
         'total_breakdown_time_jam' => $fmt(abs($totalBreakdownTime)),
         'total_running_time_jam' => $fmt($totalRunningTime > 0 ? $totalRunningTime : 0),
         'total_breakdown_perangkat' => $totalBreakdownDevices,

         // data untuk Highcharts
         'highchart' => [
            ['name' => 'Breakdown', 'y' => $persenBreakdown],
            ['name' => 'Ready', 'y' => $persenReady],
         ],

         'persentase' => [
            'breakdown' => $persenBreakdown,
            'ready' => $persenReady,
         ],

         // total root cause berdasarkan kategori perangkat
         'root_cause_count' => $rootCauseCount,

         // ✅ Data detail hasil join
         'breakdown_details' => $breakdownDetailsEnriched,

         'day_range' => $daysRange,
      ]);
   }
}
