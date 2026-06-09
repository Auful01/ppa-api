<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Dedoc\Scramble\Scramble;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use League\Csv\Reader;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use DNS2D;

class AduanController extends Controller
{
    public function index()
    {
        $auth = auth()->user()->role;
        if ($auth == 'soc_ho' || $auth == 'ict_developer') {
            # code...
            $aduan = Aduan::where('category_name', 'SOC')
                ->whereNull('deleted_at')
                ->orderByRaw("
        CASE 
            WHEN urgency = 'URGENT' AND status IN ('OPEN', 'PROGRESS', 'CLOSED') THEN 0
            ELSE 1
        END
    ")
                ->orderBy('date_of_complaint', 'desc')
                ->get();
            $countOpen = Aduan::where('status', 'OPEN')->where('category_name', 'SOC')->count();
            $countClosed = Aduan::where('status', 'CLOSED')->where('category_name', 'SOC')->count();
            $countProgress = Aduan::where('status', 'PROGRESS')->where('category_name', 'SOC')->count();
            $countCancel = Aduan::where('status', 'CANCEL')->where('category_name', 'SOC')->count();

            $crew = User::whereIn('role', ['soc_ho'])->where('site', 'HO')->pluck('name')->map(function ($name) {
                return ['name' => $name];
            })->toArray();
        } else {
            $aduan = Aduan::where('site', auth()->user()->site)
                ->whereNull('deleted_at')
                ->orderByRaw("
        CASE 
            WHEN urgency = 'URGENT' AND status IN ('OPEN', 'PROGRESS', 'CLOSED') THEN 0
            ELSE 1
        END
    ")
                ->orderBy('date_of_complaint', 'desc')
                ->get();
            $countOpen = Aduan::where('status', 'OPEN')->where('site', auth()->user()->site)->count();
            $countClosed = Aduan::where('status', 'CLOSED')->where('site', auth()->user()->site)->count();
            $countProgress = Aduan::where('status', 'PROGRESS')->where('site', auth()->user()->site)->count();
            $countCancel = Aduan::where('status', 'CANCEL')->where('site', auth()->user()->site)->count();

            $crew = User::whereIn('role', ['ict_ho'])->where('site', 'HO')->pluck('name')->map(function ($name) {
                return ['name' => $name];
            })->toArray();
        }
        return Inertia::render(
            'Aduan/Aduan',
            [
                'aduan' => $aduan,
                'open' => $countOpen,
                'closed' => $countClosed,
                'progress' => $countProgress,
                'cancel' => $countCancel,
                'crew' => $crew,
            ]
        );
    }

    public function checkAduan(Request $request)
    {
        $aduanBaru = Aduan::where('site', 'HO')->orderBy('id', 'desc')->first();

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
        $site = Auth::user()->site;
        if ($site != 'HO') {
            $categories = DB::table('root_cause_categories')
                ->select('id', 'category_root_cause')
                ->where('site_type', 'SITE')
                ->get();
        } else {
            $categories = DB::table('root_cause_categories')
                ->select('id', 'category_root_cause')
                ->where('site_type', 'HO')
                ->get();
        }

        // start generate code
        $currentDate = Carbon::now();
        $year = $currentDate->format('y');
        $month = $currentDate->month;
        $day = $currentDate->day;

        $maxId = Aduan::whereDate('created_at', $currentDate->format('Y-m-d'))->where('site', auth()->user()->site)->orderBy('max_id', 'desc')->first();

        if (is_null($maxId)) {
            $maxId = 0;
        } else {
            $split = explode('-', $maxId->complaint_code);
            $noUrut = (int) $split[2];
            $maxId = $noUrut;
        }

        $uniqueString = 'ADUAN-' . $year . $month . $day . '-' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $request['ticket'] = $uniqueString;

        $crew = User::where('site', auth()->user()->site)->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();
        // return response()->json($crew);
        // end generate code
        return Inertia::render('Aduan/AduanCreate', ['ticket' => $uniqueString, 'crew' => $crew, 'categories' => $categories]);
    }

    public function store(Request $request)
    {
        // dd($request);
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
            'site' => auth()->user()->site,
            'site_pelapor' => auth()->user()->site
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
            // return response()->json($data);
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
        return redirect()->route('aduan.page');
    }

    public function progress($id)
    {
        $aduan = Aduan::find($id);
        // Pastikan date_of_complaint dalam format ISO 8601 UTC

        // dd($aduan);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        $crew = User::where('site', auth()->user()->site)->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        // dd($aduan);
        return Inertia::render('Aduan/AduanProgress', [
            'aduan' => $aduan,
            'crew' => $crew,
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
        // start get response time
        $task = Aduan::find($request->id);
        $awal  = date_create($request->dateOfComplaint);
        $akhir = date_create($request->startResponse);
        $diff  = date_diff($awal, $akhir);
        $hTotal = $diff->d * 24 + ($diff->h);

        $jaditotal = sprintf('%02s', $hTotal) . ':' . sprintf('%02s', $diff->i) . ':' . sprintf('%02s', $diff->s);

        if ($request->dateOfComplaint != 'null') {
            $dateOfComplaintUTC = Carbon::parse($request->dateOfComplaint)->format('Y-m-d H:i:s');
        } else {
            $dateOfComplaintUTC = null;
        }

        if ($request->startResponse != 'null') {
            $dateOfResponseUTC = Carbon::parse($request->startResponse)->format('Y-m-d H:i:s');
        } else {
            $dateOfResponseUTC = null;
        }

        // dd($request->startProgress);

        if ($request->startProgress != 'null') {
            $dateOfProgressUTC = Carbon::parse($request->startProgress)->format('Y-m-d H:i:s');
        } else {
            $dateOfProgressUTC = null;
        }

        if ($request->endProgress != 'null') {
            $dateOfEndProgressUTC = Carbon::parse($request->endProgress)->format('Y-m-d H:i:s');
        } else {
            $dateOfEndProgressUTC = null;
        }

        $response_time = $jaditotal;
        // end get response time

        $data = [
            'repair_note'     => ($request->repair_note === "null" || trim($request->repair_note) === "") ? null : $request->repair_note,
            'status'          => $request->status,
            'location'        => ($request->location === "null" || trim($request->location) === "") ? null : $request->location,
            'detail_location' => ($request->detail_location === "null" || trim($request->detail_location) === "") ? null : $request->detail_location,
            'complaint_note'  => ($request->complaint_note === "null" || trim($request->complaint_note) === "") ? null : $request->complaint_note,
            'action_repair'   => ($request->actionRepair === "null" || trim($request->actionRepair) === "") ? null : $request->actionRepair,
            'date_of_complaint' => $dateOfComplaintUTC,
            'start_response'    => $dateOfResponseUTC,
            'start_progress'    => $dateOfProgressUTC,
            'end_progress'      => $dateOfEndProgressUTC,
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
        // return dd($data);

        $closing_aduan = Aduan::firstWhere('id', $request->id)->update($data);
        return redirect()->route('aduan.page');
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
        $site = Auth::user()->site;
        if ($site != 'HO') {
            $categories = DB::table('root_cause_categories')
                ->select('id', 'category_root_cause')
                ->where('site_type', 'SITE')
                ->get();
        } else {
            $categories = DB::table('root_cause_categories')
                ->select('id', 'category_root_cause')
                ->where('site_type', 'HO')
                ->get();
        }

        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }

        $selectCrew = explode(', ', $aduan->crew);
        // return dd($aduan);
        $crew = User::where('site', auth()->user()->site)->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();
        return Inertia::render('Aduan/AduanEdit', [
            'aduan' => $aduan,
            'crew' => $crew,
            'selectCrew' => $selectCrew,
            'categories' => $categories,
        ]);
    }

    public function update_aduan(Request $request)
    {
        $task = Aduan::find($request->id);
        $awal  = date_create($request->dateOfComplaint);
        $akhir = date_create($request->startResponse);
        $diff  = date_diff($awal, $akhir);
        $hTotal = $diff->d * 24 + ($diff->h);

        $jaditotal = sprintf('%02s', $hTotal) . ':' . sprintf('%02s', $diff->i) . ':' . sprintf('%02s', $diff->s);

        $response_time = $jaditotal;

        if ($request->dateOfComplaint != 'null') {
            $dateOfComplaintUTC = Carbon::parse($request->dateOfComplaint)->format('Y-m-d H:i:s');
        } else {
            $dateOfComplaintUTC = null;
        }

        if ($request->startResponse != 'null') {
            $dateOfResponseUTC = Carbon::parse($request->startResponse)->format('Y-m-d H:i:s');
        } else {
            $dateOfResponseUTC = null;
        }

        // dd($request->startProgress);

        if ($request->startProgress != 'null') {
            $dateOfProgressUTC = Carbon::parse($request->startProgress)->format('Y-m-d H:i:s');
        } else {
            $dateOfProgressUTC = null;
        }

        if ($request->endProgress != 'null') {
            $dateOfEndProgressUTC = Carbon::parse($request->endProgress)->format('Y-m-d H:i:s');
        } else {
            $dateOfEndProgressUTC = null;
        }

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
            'date_of_complaint' => $dateOfComplaintUTC,
            'start_response'    => $dateOfResponseUTC,
            'start_progress'    => $dateOfProgressUTC,
            'end_progress'      => $dateOfEndProgressUTC,
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
        return redirect()->route('aduan.page');
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
        $aduan = Aduan::where('id', $id)->first();
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        return Inertia::render('Aduan/AduanDetail', [
            'aduan' => $aduan,
        ]);
    }

    public function destroy($id)
    {
        $aduan = Aduan::find($id);
        if (empty($aduan)) {
            abort(404, 'Data not found');
        }
        // return response()->json(['ap' => $aduan]);
        $aduan->delete();
        return redirect()->back();
    }

    public function exportPdf(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'site' => 'required|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'pic' => 'nullable|string',
        ]);

        // 2. Ambil data dari request
        $site = $validated['site'];
        $startDate = $validated['startDate'] ?? null;
        $endDate = $validated['endDate'] ?? null;
        $picName = $validated['pic'] ?? null;

        $startDateConv = $startDate ? Carbon::parse($startDate)->translatedFormat('d F Y') : null;
        $endDateConv = $endDate ? Carbon::parse($endDate)->translatedFormat('d F Y') : null;

        // 3. Ambil data inspeksi berdasarkan device
        $dataAduan = $this->getDataAduan($site, $startDate, $endDate);
        // dd($dataAduan);

        // 5. Cari data yang berstatus "Y" untuk mendapatkan PIC approval
        if ($site == 'HO') {
            $picApproved = 'EDI NUGROHO';
        } else {
            if (auth()->user()->role == 'ict_group_leader') {
                $picApproved = auth()->user()->name;
            } else {
                $picApproved = 'Export Menggunakan Akun GL!';
            }
        }
        $qr_base64Approved = $picApproved ? $this->generateQrFromUserName($picApproved) : null;

        // 6. Generate QR untuk PIC inspeksi (yang dipilih manual)
        $qr_base64Pic = $picName ? $this->generateQrFromUserName($picName) : null;

        // 7. Generate PDF
        Pdf::setOptions(['isRemoteEnabled' => true]); // <--- WAJIB agar gambar URL bisa muncu

        // 8. Return PDF response
        if ($startDate && $endDate) {
            $pdf = Pdf::loadView('itportal.rekapAllInspeksi.rekapAduan', compact(
                'dataAduan',
                'site',
                'picName',
                'picApproved',
                'qr_base64Approved',
                'qr_base64Pic',
                'startDateConv',
                'endDateConv',
            ))->setPaper('A4', 'landscape');
            return $pdf->stream('Data-aduan-' . '-report-periode' . $startDate . '-' . $endDate . 'pdf');
        } else {
            $year = Carbon::now()->year;
            $pdf = Pdf::loadView('itportal.rekapAllInspeksi.rekapAduan', compact(
                'dataAduan',
                'site',
                'picName',
                'picApproved',
                'qr_base64Approved',
                'qr_base64Pic',
                'year',
            ))->setPaper('A4', 'landscape');
            return $pdf->stream('Data-aduan-' . '-report-periode' . $year . 'pdf');
        }
    }

    private function getDataAduan($site, $startDate, $endDate)
    {
        $query = Aduan::where('site', $site);

        if ($startDate && $endDate) {
            $query->whereBetween('created_date', [$startDate, $endDate]);
        }

        return $query->get();
    }

    private function generateQrFromUserName($name)
    {
        $user = User::where('name', $name)->first();
        if (!$user) return null;

        $qrString = "NRP: {$user->nrp}, Nama: {$user->name}, Jabatan: {$user->position}";
        $barcode = new \Milon\Barcode\DNS2D();
        $barcode->setStorPath(storage_path('framework/barcodes/'));
        $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');

        return 'data:image/png;base64,' . $pngData;
    }
}
