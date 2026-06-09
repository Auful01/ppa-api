<?php

namespace App\Http\Controllers;

use App\Models\InspeksiPrinter;
use App\Models\InvPrinter;
use App\Models\PicaInspeksi;
use App\Models\SchedulePrinter;
use App\Models\User;
use App\Models\UserAll;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InspeksiPrinterPikController extends Controller
{
    public function index(Request $request)
    {
        $site = 'PIK';
        $bulanNow = $request->input('month', now()->month);
        $yearNow = $request->input('year', now()->year);

        $inspeksi_printer = InspeksiPrinter::with('printer')->where('site', $site)->where('month', $bulanNow)
            ->where('year', $yearNow)->get();
        // dd($inspeksiPrinter);

        $role = auth()->user()->role;

        $crew = User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $site)->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        $bulan_sekarang = now()->month;
        $tahun_sekarang = now()->year;

        return Inertia::render(
            'Inspeksi/SitePik/Printer/InspeksiPrinterView',
            [
                'inspeksiPrinterx' => $inspeksi_printer,
                'site' => $site,
                'role' => $role,
                'monthNow' => (int) $bulanNow,
                'yearNow' => (int) $yearNow,
                'bulan_sekarang' => (int) $bulan_sekarang,
                'tahun_sekarang' => (int) $tahun_sekarang,
                'crew' => $crew
            ]
        );
    }

    public function process($id)
    {
        $dataInspeksix = InspeksiPrinter::find($id);
        if (empty($dataInspeksix)) {
            abort(404, 'Data not found');
        }

        $printerx = InvPrinter::where('inv_printers.id', $dataInspeksix->inv_printer_id)->first();
        if (empty($printerx)) {
            abort(404, 'Data not found');
        }

        // dd($printerx);

        $penggunax = User::whereIn('role', ['ict_technician', 'ict_group_leader', 'ict_developer'])->where('site', 'PIK')->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();
        // dd($penggunax);
        return Inertia::render('Inspeksi/SitePik/Printer/InspeksiPrinterCreate', ['dataInspeksi' => $dataInspeksix, 'pengguna' => $penggunax, 'printer' => $printerx]);
    }

    public function store(Request $request)
    {
        // dd($request);
        $params = $request->all();
        $currentDate = Carbon::now();
        $month = $currentDate->format('m');
        $year = $currentDate->format('Y');

        // dd($request->file('image_temuan'));

        $maxId = InspeksiPrinter::where('site', "PIK")->where('year', $year)->where('month', $month)->max('pica_number');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        // $no_pica = 'PICA/CU/' . $year . '/' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $no_pica = $maxId + 1;

        $data = [
            'tinta_cyan' => $params['tinta_cyan'],
            'tinta_magenta' => $params['tinta_magenta'],
            'tinta_yellow' => $params['tinta_yellow'],
            'tinta_black' => $params['tinta_black'],
            'body_condition' => $params['body_condition'],
            'usb_cable_condition' => $params['usb_cable_condition'],
            'power_cable_condition' => $params['power_cable_condition'],
            'performing_physical_power_cleaning' => $params['performing_physical_power_cleaning'],
            'performing_cleaning_on_the_printer_waste_box' => $params['performing_cleaning_on_the_printer_waste_box'],
            'performing_cleaning_head' => $params['performing_cleaning_head'],
            'performing_print_quality_test' => $params['performing_print_quality_test'],
            'do_replacing_cable' => $params['do_replacing_cable'],

            'condition' => $params['condition'],
            'inventory_status' => $params['status_inv'],
            'findings' => $params['temuan'],
            'findings_action' => $params['tindakan'],
            'due_date' => $params['due_date'],
            'findings_status' => $params['findings_status'],
            'remarks' => $params['remark'],
            'inspector' => $params['inspector'],
            'inspection_status' => $params['inspection_status'],
            'inspection_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'last_edited_by' => auth()->user()->nrp
        ];

        if ($params['temuan'] != null || $params['temuan'] != '') {
            $data['pica_number'] = $no_pica;

            $dataPica = [
                'pica_number' => $no_pica,
                'inspeksi_id' => $request->id,
                'temuan' => $params['temuan'],
                'tindakan' => $params['tindakan'],
                'due_date' => $params['due_date'],
                'remark' => $params['remark'],
                'status_pica' => $params['findings_status'],
                'close_by' => auth()->user()->name,
                'site' => 'PIK',
            ];
        }

        if ($request->file('image_temuan') != null) {
            $document_image = $request->file('image_temuan');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['findings_image'] = url($new_path_document_image);
            $dataPica['foto_temuan'] = url($new_path_document_image);
        }

        if ($request->file('image_tindakan') != null) {
            $document_image = $request->file('image_tindakan');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['action_image'] = url($new_path_document_image);
            $dataPica['foto_tindakan'] = url($new_path_document_image);
        }

        if ($request->file('image_inspeksi') != null) {
            $document_image = $request->file('image_inspeksi');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['inspection_image'] = url($new_path_document_image);
        }

        if ($request->file('image_nozle') != null) {
            $document_image = $request->file('image_nozle');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['nozle_image'] = url($new_path_document_image);
        }
        // dd($dataPica);
        if ($params['temuan']) {
            PicaInspeksi::create($dataPica);
        }
        InspeksiPrinter::firstWhere('id', $request->id)->update($data);

        $dataSchedule = ['actual_inspection' => Carbon::now()->format('Y-m-d H:i:s')];

        $dataInspeksix = InspeksiPrinter::find($request->id);

        if (empty($dataInspeksix)) {
        } else {
            SchedulePrinter::where('id_printer', $dataInspeksix->inv_printer_id)->where('tahun', $year)->where('bulan', $month)->first()->update($dataSchedule);
        }

        return redirect()->route('inspeksiPrinterPik.page');
    }
    public function edit($id)
    {
        $dataInspeksix = InspeksiPrinter::find($id);
        if (empty($dataInspeksix)) {
            abort(404, 'Data not found');
        }

        $printerx = InvPrinter::where('inv_printers.id', $dataInspeksix->inv_printer_id)->first();
        if (empty($printerx)) {
            abort(404, 'Data not found');
        }

        // dd($printerx);

        if (!empty($dataInspeksix->inspector)) {

            $pengguna_selected = array($dataInspeksix->inspector);
        } else {
            $pengguna_selected = array('data tidak ada !');
        }

        $penggunax = User::whereIn('role', ['ict_technician', 'ict_group_leader', 'ict_developer'])->where('site', 'PIK')->pluck('name')->map(function ($name) {
            return ['name' => $name];
        })->toArray();

        return Inertia::render('Inspeksi/SitePik/Printer/InspeksiPrinterEdit', ['dataInspeksi' => $dataInspeksix, 'pengguna' => $penggunax, 'printer' => $printerx, 'pengguna_selected' => $pengguna_selected]);
    }

    public function update(Request $request)
    {
        $params = $request->all();
        // dd($params);
        $currentDate = Carbon::now();
        $month = $currentDate->format('m');
        $year = $currentDate->format('Y');

        // dd($request->file('image_temuan'));

        $maxId = InspeksiPrinter::where('site', 'PIK')->where('year', $year)->where('month', $month)->max('pica_number');

        if (is_null($maxId)) {
            $maxId = 0;
        }

        // $no_pica = 'PICA/CU/' . $year . '/' . str_pad(($maxId % 10000) + 1, 2, '0', STR_PAD_LEFT);
        $no_pica = $maxId + 1;

        $data = [
            'tinta_cyan' => $params['tinta_cyan'],
            'tinta_magenta' => $params['tinta_magenta'],
            'tinta_yellow' => $params['tinta_yellow'],
            'tinta_black' => $params['tinta_black'],
            'body_condition' => $params['body_condition'],
            'usb_cable_condition' => $params['usb_cable_condition'],
            'power_cable_condition' => $params['power_cable_condition'],
            'performing_physical_power_cleaning' => $params['performing_physical_power_cleaning'],
            'performing_cleaning_on_the_printer_waste_box' => $params['performing_cleaning_on_the_printer_waste_box'],
            'performing_cleaning_head' => $params['performing_cleaning_head'],
            'performing_print_quality_test' => $params['performing_print_quality_test'],
            'do_replacing_cable' => $params['do_replacing_cable'],

            'condition' => $params['condition'],
            'inventory_status' => $params['status_inv'],
            'findings' => $params['temuan'],
            'findings_action' => $params['tindakan'],
            'due_date' => $params['due_date'],
            'findings_status' => $params['findings_status'],
            'remarks' => $params['remark'],
            'inspector' => $params['inspector'],
            'inspection_status' => $params['inspection_status'],
            'inspection_at' => Carbon::now()->format('Y-m-d H:i:s'),
            'last_edited_by' => auth()->user()->nrp
        ];

        if ($params['temuan'] != null || $params['temuan'] != '') {
            $dataInspeksix = InspeksiPrinter::find($request->id);
            if ($dataInspeksix->pica_number == null) {
                $data['pica_number'] = $no_pica;
            }
            $dataPica = [
                'pica_number' => $no_pica,
                'inspeksi_id' => $request->id,
                'temuan' => $params['temuan'],
                'tindakan' => $params['tindakan'],
                'due_date' => $params['due_date'],
                'remark' => $params['remark'],
                'status_pica' => $params['findings_status'],
                'close_by' => auth()->user()->name,
                'site' => 'PIK',
            ];
        }

        if ($request->file('image_temuan') != null) {
            $document_image = $request->file('image_temuan');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['findings_image'] = url($new_path_document_image);
            $dataPica['foto_temuan'] = url($new_path_document_image);
        }

        if ($request->file('image_tindakan') != null) {
            $document_image = $request->file('image_tindakan');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['action_image'] = url($new_path_document_image);
            $dataPica['foto_tindakan'] = url($new_path_document_image);
        }

        if ($request->file('image_inspeksi') != null) {
            $document_image = $request->file('image_inspeksi');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['inspection_image'] = url($new_path_document_image);
        }

        if ($request->file('image_nozle') != null) {
            $document_image = $request->file('image_nozle');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['nozle_image'] = url($new_path_document_image);
        }

        if ($params['temuan']) {
            $pica_printer = PicaInspeksi::where('inspeksi_id', $request->id)->first();

            // dd($request->id);

            if (empty($pica_printer)) {
                PicaInspeksi::create($dataPica);
            } else {
                PicaInspeksi::firstWhere('inspeksi_id', $request->id)->update($dataPica);
            }
        }
        InspeksiPrinter::firstWhere('id', $request->id)->update($data);
        return redirect()->route('inspeksiPrinterPik.page');
    }

    public function detail($id)
    {
        $inspeksi_printer = InspeksiPrinter::with('printer')->where('inspeksi_printers.id', $id)->first();

        if (empty($inspeksi_printer)) {
            abort(404, 'Data not found');
        }

        // dd($inspeksi_printer);

        return Inertia::render('Inspeksi/SitePik/Printer/InspeksiPrinterDetail', [
            'inspeksi' => $inspeksi_printer
        ]);
    }

    public function approval(Request $request)
    {
        $bulanNow = $request->input('month', now()->month);
        $yearNow = $request->input('year', now()->year);
        $user = Auth::user();

        // Cek apakah user memiliki role 'ict_group_leader'
        if ($user->role !== 'ict_group_leader') {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, Anda tidak dapat melakukan approval dikarenakan role Anda bukan GROUP LEADER!',
            ], 403);
        }

        $site = 'PIK';
        if (!$site) {
            return response()->json([
                'success' => false,
                'message' => 'Site user tidak ditemukan.',
            ], 400);
        }

        $dataApproval = [
            'approved_by' => $user->name,
            'status_approval' => 'approved',
        ];

        $updateCount = InspeksiPrinter::where('site', $site)
            ->where('month', $bulanNow)
            ->where('year', $yearNow)
            ->where('inspection_status', 'Y')
            ->update($dataApproval);

        return response()->json([
            'success' => true,
            'message' => "$updateCount data inspeksi Printer untuk site $site telah di-approve.",
        ]);
    }

    public function show($id)
    {
        $inspeksi_printer = InspeksiPrinter::find($id);
        if (is_null($inspeksi_printer)) {
            return response()->json(['message' => 'Panelbox Data not found'], 404);
        }
        return response()->json($inspeksi_printer);
    }

    public function destroy($id)
    {
        $inspeksi_printer = InspeksiPrinter::find($id);
        if (is_null($inspeksi_printer)) {
            return response()->json(['message' => 'Panelbox Data not found'], 404);
        }
        $inspeksi_printer->delete();
        return response()->json(['message' => 'Data has deleted'], 204);
    }
}
