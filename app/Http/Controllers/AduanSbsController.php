<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\RootCauseProblem;
use App\Models\InvCctv;
use App\Models\InvComputer;
use App\Models\InvLaptop;
use App\Models\InvPrinter;
use App\Models\PerangkatBreakdown;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;

class AduanSbsController extends Controller
{
    public function index()
    {

        $aduan = Aduan::with('rootCause')
            ->where('site', 'SBS')
            ->whereNull('deleted_at')
            ->orderByRaw("
        CASE 
            WHEN urgency = 'URGENT' AND status IN ('OPEN', 'PROGRESS', 'CLOSED') THEN 0
            ELSE 1
        END
    ")
            ->orderBy('date_of_complaint', 'desc')
            ->get();
        $countOpen = Aduan::where('status', 'OPEN')->where('site', 'SBS')->count();
        $countClosed = Aduan::where('status', 'CLOSED')->where('site', 'SBS')->count();
        $countProgress = Aduan::where('status', 'PROGRESS')->where('site', 'SBS')->count();
        $countCancel = Aduan::where('status', 'CANCEL')->where('site', 'SBS')->count();

        $crew = User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', 'SBS')->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        return Inertia::render(
            'Inventory/SiteSbs/Aduan/Aduan',
            [
                'aduan' => $aduan,
                'crew' => $crew,
                'open' => $countOpen,
                'closed' => $countClosed,
                'progress' => $countProgress,
                'cancel' => $countCancel,
            ]
        );
    }

    public function checkAduan(Request $request)
    {
        $aduanBaru = Aduan::where('site', 'SBS')->orderBy('id', 'desc')->first();

        if ($aduanBaru) {
            $response = [
                'id' => $aduanBaru->max_id,
                'site' => $aduanBaru->site,
                'message' => $aduanBaru->complaint_note
            ];

            if ($request->wantsJson() || $request->header('X-Inertia') !== 'true') {
                return response()->json($response);
            }
        }
    }

    public function create()
    {
        $categories = DB::table('root_cause_categories')
            ->select('id', 'category_root_cause')
            ->where('site_type', 'SITE')
            ->get();

        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = Aduan::whereDate('created_at', $currentDate->format('Y-m-d'))->where('site', 'SBS')->orderBy('max_id', 'desc')->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            $split = explode('-', $maxId->complaint_code);
            $noUrut = (int) $split[2];
            $maxId = $noUrut;
        }

        $uniqueString = 'ADUAN-' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['ticket'] = $uniqueString;

        $crew = User::where('site', 'SBS')->where('ict_group', 'Y')->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        return Inertia::render('Inventory/SiteSbs/Aduan/AduanCreate', ['ticket' => $uniqueString, 'crew' => $crew, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        $maxId = Aduan::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }

        $data = [
            'max_id' => $maxId,
            'nrp' => $request['nrp'],
            'complaint_name' => $request['complaint_name'],
            'complaint_note' => $request['complaint_note'],
            'phone_number' => $request['phone_number'],
            'date_of_complaint' => $request['date_of_complaint'],
            'location' => $request['location'],
            'detail_location' => $request['location_detail'],
            'category_name' => $request['category_name'],
            'crew' => $request['crew'],
            'site' => 'SBS',
            'site_pelapor' => 'SBS'
        ];

        if ($request->file('image') != null) {
            $documentation_image = $request->file('image');
            $destinationPath = 'images/';
            $path_documentation_image = $documentation_image->store('images', 'public');
            $new_path_documentation_image = $path_documentation_image;
            $documentation_image->move($destinationPath, $new_path_documentation_image);

            $data['complaint_image'] =  url($new_path_documentation_image);
        }

        $data['complaint_code'] = $request->complaint_code;
        $data['created_date'] = Carbon::parse($request->date_of_complaint)->toDateString();

        if (!empty($request->inventory_number)) {
            $aduan_get_data_user = UserAll::where('nrp', $request->nrp)->first();
            if (!empty($aduan_get_data_user)) {
                $data['complaint_position'] = $aduan_get_data_user['position'];
            } else {
                $data['complaint_position'] = 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)';
            }
            $data['inventory_number'] = $request->inventory_number;
            $data['status'] = 'OPEN';
            $aduan = Aduan::create($data);
        } else {
            $aduan_get_data_user = UserAll::where('nrp', $request->nrp)->first();
            if (!empty($aduan_get_data_user)) {
                $data['complaint_position'] = $aduan_get_data_user['position'];
            } else {
                $data['complaint_position'] = 'User Belum Terdaftar Pada Sistem (NRP Not Detect!)';
            }
            $data['status'] = 'OPEN';

            $aduan = Aduan::create($data);
        }
        return redirect()->route('aduanSbs.page');
    }

    public function progress($id)
    {
        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        $crew = User::where('site', 'SBS')->where('ict_group', 'Y')->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

                // ambil root cause sesuai kategori aduan
        $rootCauses = RootCauseProblem::where('kategori_name', $aduan->category_name)
            ->get(['id_cause as id', 'root_cause_problem as name'])
            ->toArray(); // ambil hanya id & problemnya biar ringan

        return Inertia::render('Inventory/SiteSbs/Aduan/AduanProgress', [
            'aduan' => $aduan,
            'crew' => $crew,
            'rootCause' => $rootCauses,
        ]);
    }

     public function accept(Request $request, $id)
    {
        try {
            $aduan = Aduan::findOrFail($id);

            $aduan->start_response = now();
            $aduan->status = 'PROGRESS';

            $awal  = Carbon::parse($aduan->date_of_complaint);
            $akhir = Carbon::parse($aduan->start_response);

            // total selisih dalam detik
            $diffInSeconds = $awal->diffInSeconds($akhir);

            // ubah ke format H:i:s (total jam bisa lebih dari 24)
            $jam   = floor($diffInSeconds / 3600);
            $menit = floor(($diffInSeconds % 3600) / 60);
            $detik = $diffInSeconds % 60;

            $responseTime = sprintf('%02d:%02d:%02d', $jam, $menit, $detik);

            $aduan->response_time = $responseTime;
            $aduan->save();

            return response()->json([
                'success' => true,
                'message' => 'Aduan berhasil diterima dan status diubah menjadi PROGRESS',
                'data' => $aduan,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menerima aduan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update_aduan_progress(Request $request)
    {

        if ($request->startProgress == 'null' || $request->startProgress == null) {
            $startProgress = null;
        } else {
            $startProgress = $request->startProgress;
        }

        if ($request->endProgress == 'null' || $request->endProgress == null) {
            $endProgress = null;
        } else {
            $endProgress = $request->endProgress;
        }

        if ($request->startResponse == 'null' || $request->startResponse == null) {
            $startResponse = null;
        } else {
            $startResponse = $request->startResponse;
        }

        $task = Aduan::find($request->id);
        $awal  = date_create($request->dateOfComplaint);
        $akhir = date_create($request->startResponse);
        $diff  = date_diff($awal, $akhir);
        $hTotal = $diff->d * 24 + ($diff->h);

        $jaditotal = sprintf('%02s', $hTotal) . ':' . sprintf('%02s', $diff->i) . ':' . sprintf('%02s', $diff->s);

        $response_time = $jaditotal;
        // end get response time

        $data = [
            'repair_note' => $request->repair_note,
            'status' => $request->status,
            'location' => $request->location,
            'detail_location' => $request->detail_location,
            'complaint_note' => $request->complaint_note,
            'action_repair' => $request->actionRepair,
            'date_of_complaint' => $request->dateOfComplaint,
            'start_response' => $startResponse,
            'start_progress' => $startProgress,
            'end_progress' => $endProgress,
            'root_cause_id' => $request->rootCause,
        ];
        if ($request->crew != null || $request->crew != '') {
            $data['crew'] = $request->crew;
        }
        if ($request->file('image') != null) {
            $documentation_image = $request->file('image');
            $destinationPath = 'images/';
            $path_documentation_image = $documentation_image->store('images', 'public');
            $new_path_documentation_image = $path_documentation_image;
            $documentation_image->move($destinationPath, $new_path_documentation_image);

            $data['repair_image'] =  url($new_path_documentation_image);
        }


        $data['response_time'] = $response_time;

        $closing_aduan = Aduan::firstWhere('id', $request->id)->update($data);

                // Jika status close → simpan ke perangkat_breakdown
        if ($request->status === 'CLOSED' && !empty($task->complaint_code)) {

            // Daftar mapping kategori ke root cause (pakai nama root cause, bukan ID)
            $validRootCauses = [
                'PC/NB'      => ['RAM', 'MONITOR', 'KABEL', 'OS', 'DRIVER', 'HARDISK', 'SOFTWARE', 'LAIN-LAIN'],
                'TELKOMSEL'  => ['LINK METRO', 'LINK BTS', 'POWER BTS', 'RECTIFIER', 'SECTORAL'],
                'NETWORK'    => ['LINK BACKBONE', 'ISP', 'POWER', 'KONFIGURASI', 'ACCESS POINT'],
                'SERVER'     => ['POWER', 'RAM', 'STORAGE', 'CPU', 'UPS', 'OS'],
                'CCTV'       => ['LINK/BACKBONE', 'CAMERA', 'SHORT CABLE', 'POWER', 'NVR'],
                'PRINTER'    => ['TINTA HABIS', 'TINTA BOCOR', 'PERLU RESET', 'SENSOR', 'KOMPONEN', 'LAIN-LAIN'],
                'NETWORK MT' => ['BATRAI', 'SOLAR PANEL', 'MPPT', 'BACKBONE', 'ACCESS POINT', 'KABEL'],
                'GPS'        => ['POWER', 'KARTU', 'KUOTA', 'BATRAI GPS', 'UNIT GPS'],
            ];

            // ambil root cause dari tabel berdasarkan ID
            $rootCause = RootCauseProblem::find($request->rootCause);

            if (!$rootCause) {
                throw new \Exception("Root cause dengan ID {$request->rootCause} tidak ditemukan");
            }

            // cek apakah kategori ada di mapping
            if (array_key_exists($task->category_name, $validRootCauses)) {

                // cek apakah nama root cause ada di daftar valid untuk kategori tersebut
                if (in_array($rootCause->root_cause_problem, $validRootCauses[$task->category_name])) {

                    $updateData = [
                        'pic'      => $request->crew,
                        'end_time' => $endProgress ?? Carbon::now(),
                        'status'   => 'CLOSED',
                    ];

                    $perangkat = PerangkatBreakdown::where('id_report', $task->complaint_code)
                        ->where('status', 'OPEN')
                        ->latest('start_time')
                        ->first();

                    if ($perangkat) {
                        // update
                        $perangkat->update($updateData);
                    } else {
                        // ambil data aduan berdasarkan complaint_code
                        $aduan = Aduan::where('complaint_code', $task->complaint_code)->first();

                        $deviceName = 'Unknown Device';
                        $categoryInput = strtolower($aduan->category_name);
                        $invNumber = strtoupper($aduan->inventory_number); // biar aman case insensitive

                        if ($categoryInput === 'pc/nb') {
                            // Cek dari inventory_number
                            if (str_contains($invNumber, '-NB-')) {
                                $inv = InvLaptop::where('laptop_code', $aduan->inventory_number)->first();
                                $deviceName = $inv ? $inv->laptop_name : $deviceName;
                            } elseif (str_contains($invNumber, '-PC-')) {
                                $inv = InvComputer::where('computer_code', $aduan->inventory_number)->first();
                                $deviceName = $inv ? $inv->computer_name : $deviceName;
                            }
                        } elseif ($categoryInput === 'printer') {
                            $inv = InvPrinter::where('printer_code', $aduan->inventory_number)->first();
                            $deviceName = $inv ? $inv->printer_brand : $deviceName;
                        } elseif ($categoryInput === 'cctv') {
                            $inv = InvCctv::where('cctv_code', $aduan->inventory_number)->first();
                            $deviceName = $inv ? $inv->cctv_brand : $deviceName;
                        }

                        if (!$aduan) {
                            throw new \Exception("Aduan dengan complaint code {$task->complaint_code} tidak ditemukan");
                        }

                        $now = Carbon::now();

                        // siapkan data untuk insert
                        $insertData = array_merge($updateData, [
                            'id_report'  => $task->complaint_code,
                            'inventory_number' => $aduan->inventory_number,
                            'device_name'      => $deviceName,
                            'device_category'   => $aduan->category_name ?? null,
                            'pic'       => $request->crew,
                            'start_time' => $aduan->date_of_complaint ?? Carbon::now(),
                            'created_date'     => $now->toDateString(),  // hanya tanggal
                            'month'            => $now->month,           // angka 1-12
                            'year'             => $now->year,            // angka tahun
                            'root_cause'             => $rootCause->root_cause_problem,            // angka tahun
                            'root_cause_category'             => $aduan->category_name,            // angka tahun
                            'location'         => $request->location,
                            'status'           => $request->status,
                            'site'             => Auth::user()->site,
                        ]);

                        PerangkatBreakdown::create($insertData);
                    }
                } else {
                    // root cause tidak valid
                    throw new \Exception("Root cause {$rootCause->root_cause_problem} tidak sesuai dengan kategori {$task->category_name}");
                }
            }
        }

        return redirect()->route('aduanSbs.page');
    }

    public function updateUrgency(Request $request)
    {

        $request->validate([
            'id' => 'required|uuid', // atau 'required|integer' sesuai dengan tipe ID kamu
            'urgency' => 'required|in:NORMAL,URGENT',
        ]);

        $aduan = Aduan::findOrFail($request->id);
        $aduan->urgency = $request->urgency;
        $aduan->save();

        return response()->json(['message' => 'Urgency updated successfully']);
    }

    public function edit($id)
    {
        $categories = DB::table('root_cause_categories')
            ->select('id', 'category_root_cause')
            ->where('site_type', 'SITE')
            ->get();

        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }

        $selectCrew = explode(', ', $aduan->crew);
        $crew = User::where('site', 'SBS')->where('ict_group', 'Y')->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();
        return Inertia::render('Inventory/SiteSbs/Aduan/AduanEdit', [
            'aduan' => $aduan,
            'crew' => $crew,
            'selectCrew' => $selectCrew,
            'categories' => $categories,
        ]);
    }

    public function update_aduan(Request $request)
    {

        if ($request->startProgress == 'null' || $request->startProgress == null) {
            $startProgress = null;
        } else {
            $startProgress = $request->startProgress;
        }

        if ($request->endProgress == 'null' || $request->endProgress == null) {
            $endProgress = null;
        } else {
            $endProgress = $request->endProgress;
        }

        if ($request->startResponse == 'null' || $request->startResponse == null) {
            $startResponse = null;
        } else {
            $startResponse = $request->startResponse;
        }

        $task = Aduan::find($request->id);
        $awal  = date_create($request->dateOfComplaint);
        $akhir = date_create($request->startResponse);
        $diff  = date_diff($awal, $akhir);
        $hTotal = $diff->d * 24 + ($diff->h);

        $jaditotal = sprintf('%02s', $hTotal) . ':' . sprintf('%02s', $diff->i) . ':' . sprintf('%02s', $diff->s);

        $response_time = $jaditotal;
        // end get response time

        $data = [
            'complaint_name' => $request->complaint_name,
            'nrp' => $request->nrp,
            'phone_number' => $request->phone_number,
            'category_name' => $request->category_name,
            'status' => $request->status,
            'repair_note' => $request->repair_note,
            'location' => $request->location,
            'detail_location' => $request->detail_location,
            'complaint_note' => $request->complaint_note,
            'action_repair' => $request->action_repair,
            'date_of_complaint' => $request->dateOfComplaint,
            'start_response' => $startResponse,
            'start_progress' => $startProgress,
            'end_progress' => $endProgress,
        ];
        if ($request->crew != null || $request->crew != '') {
            $data['crew'] = $request->crew;
        }
        if ($request->file('image') != null) {
            $documentation_image = $request->file('image');
            $destinationPath = 'images/';
            $path_documentation_image = $documentation_image->store('images', 'public');
            $new_path_documentation_image = $path_documentation_image;
            $documentation_image->move($destinationPath, $new_path_documentation_image);

            $data['repair_image'] =  url($new_path_documentation_image);
        }

        if (!empty($request->inventory_number)) {
            $aduan_get_data_user = UserAll::where('nrp', $request->nrp)->first();
            $data['complaint_position'] = $aduan_get_data_user['position'];
            $data['inventory_number'] = $request->inventory_number;
        } else {
            $aduan_get_data_user = UserAll::where('nrp', $request->nrp)->first();
            $data['complaint_position'] = $aduan_get_data_user['position'];
        }


        $data['response_time'] = $response_time;
        // return dd($data);

        $closing_aduan = Aduan::firstWhere('id', $request->id)->update($data);
        return redirect()->route('aduanSbs.page');
    }

    public function show($id)
    {
        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        if (is_null($aduan)) {
            return response()->json(['message' => 'Aduan Data not found'], 404);
        }
        return response()->json($aduan);
    }

    public function detail($id)
    {
        $aduan = Aduan::with('rootCause')->where('id', $id)->first();
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('Inventory/SiteSbs/Aduan/AduanDetail', [
            'aduan' => $aduan,
        ]);
    }

    public function destroy($id)
    {
        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        $aduan->delete();
        return redirect()->back();
    }
}
