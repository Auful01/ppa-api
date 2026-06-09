<?php

namespace App\Http\Controllers;

use App\Models\InspeksiComputer;
use App\Models\InspeksiLaptop;
use App\Models\InspeksiMobileTower;
use App\Models\InspeksiPrinter;
use App\Models\PicaInspeksi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use DNS2D;

class PicaInspeksiController extends Controller
{
    public function index($site)
    {
        $sitePharse = $site;
        if ($sitePharse != 'HO') {
            $crew = User::whereIn('role', ['ict_technician', 'ict_group_leader'])->where('site', $sitePharse)->pluck('name')->map(function ($name) {
                return ['name' => $name];
            })->toArray();
        } else {
            $crew = User::whereIn('role', ['ict_ho'])->where('site', 'HO')->pluck('name')->map(function ($name) {
                return ['name' => $name];
            })->toArray();
        }
        // dd($dataPica);
        return Inertia::render('PicaInspeksi/PicaInspeksiIndex', ['site' => $sitePharse, 'crew' => $crew]);
    }

    public function getDataPicaByDevice(Request $request)
    {
        $deviceType = $request->input('device_type');
        $site       = (string) $request->input('site');
        // $deviceType = 'Printer';
        // $site       = 'BIB';
        $statusPica = $request->input('status_pica'); // OPTIONAL
        $startDate  = $request->input('startDate');   // OPTIONAL
        $endDate    = $request->input('endDate');     // OPTIONAL

        if (!$deviceType || !$site) {
            return response()->json([
                'message' => 'device_type dan site wajib dikirim'
            ], 400);
        }

        switch ($deviceType) {

            case 'Laptop':
                $query = InspeksiLaptop::where('site', $site)
                    ->whereNotNull('findings_image')
                    ->whereHas('inventory')
                    ->whereHas('inventory.pengguna')
                    ->with('inventory.pengguna');
                break;

            case 'Computer':
                $query = InspeksiComputer::where('site', $site)
                    ->whereNotNull('findings_image')
                    ->whereHas('computer')
                    ->whereHas('computer.pengguna')
                    ->with('computer.pengguna');
                break;

            case 'Mobile Tower':
                $query = PicaInspeksi::where('site', $site)
                    ->whereHas('inspeksiMt')
                    ->whereHas('inspeksiMt.mt')
                    ->with([
                        'inspeksiMt',
                        'inspeksiMt.mt'
                    ]);
                break;

            case 'Printer':
                $query = InspeksiPrinter::where('site', $site)
                    ->whereNotNull('findings_image')
                    ->whereHas('printer')
                    ->with('printer');
                break;

            default:
                return response()->json([
                    'message' => 'Invalid device type'
                ], 400);
        }

        if ($startDate && $endDate) {
            $start = Carbon::parse($startDate)->startOfDay();
            $end   = Carbon::parse($endDate)->endOfDay();

            if ($deviceType === 'Mobile Tower') {
                $query->whereBetween('created_at', [$start, $end]);
            } else {
                $query->whereBetween('created_date', [$startDate, $endDate]);
            }
        }

        if ($statusPica) {
            if ($deviceType === 'Mobile Tower') {
                $query->where('status_pica', $statusPica);
            } else {
                $query->where('findings_status', $statusPica);
            }
        }

        return response()->json($query->get());
    }


    public function edit($id)
    {
        if ($data = InspeksiLaptop::with('inventory.pengguna')->find($id)) {

            $inv = $data->inventory;

            $data->device_code = $inv->laptop_code ?? '-';
            $data->device_name = strtoupper($inv->laptop_name) ?? '-';
            $data->device_sn = $inv->serial_number ?? '-';
            $data->device_condition = $inv->condition ?? '-';
            $data->device_asset_ho = $inv->number_asset_ho ?? '-';
            $data->device_spesifikasi = $inv->spesifikasi ?? '-';
            $data->device_status_inventory = $inv->status ?? '-';
            $data->device_note = $inv->note ?? '-';
            $data->device_pengguna = $inv->pengguna->username ?? '-';
            $data->device_dept = $inv->pengguna->department ?? '-';
            $data->device_ip_address = $inv->ip_address ?? '-';
            $data->device_location = $inv->location ?? '-';
            $data->device_type = 'Laptop';
        } elseif ($data = InspeksiPrinter::with('printer')->find($id)) {
            $prt = $data->printer;

            $data->device_code = $prt->printer_code ?? '-';
            $data->device_name = strtoupper($prt->item_name) ?? '-';
            $data->device_sn = $prt->serial_number ?? '-';
            $data->device_condition = $data->condition ?? '-';
            $data->device_asset_ho = $prt->asset_ho_number ?? '-';
            $data->device_spesifikasi = '-';
            $data->device_status_inventory = $prt->status ?? '-';
            $data->device_note = $prt->note ?? '-';
            $data->device_pengguna = $prt->division ?? '-';
            $data->device_dept = $prt->pengguna->department ?? '-';
            $data->device_ip_address = $prt->ip_address ?? '-';
            $data->device_location = $prt->location ?? '-';
            $data->device_type = 'Printer';
        } elseif ($data = PicaInspeksi::with('inspeksiMt.mt')->find($id)) {
            // dd($data);
            $mt = $data->inspeksiMt;

            $data->device_code = $mt->mt->inventory_number ?? '-';
            $data->device_name = strtoupper($mt->mt->mt_code) ?? '-';
            $data->device_sn = '-';
            $data->device_condition = $mt->condition ?? '-';
            $data->device_asset_ho = '-';
            $data->device_spesifikasi = '-';
            $data->device_status_inventory = $mt->mt->status ?? '-';
            $data->device_note = $mt->mt->note ?? '-';
            $data->device_pengguna = '-';
            $data->device_dept = '-';
            $data->device_ip_address = '-';
            $data->device_location = $mt->mt->location ?? '-';
            $data->device_type = 'Mobile Tower';
            $data->findings = $data->temuan;
            $data->findings_image = $data->foto_temuan;
            $data->findings_action = $data->tindakan;
            $data->action_image = $data->foto_tindakan;
            $data->findings_status = $data->status_pica;
            $data->remarks = $data->remark;
            $data->inspector = $mt->pic;
            $data->inspection_image = $mt->inspection_image;
        } elseif ($data = InspeksiComputer::with('computer.pengguna')->find($id)) {

            $cmp = $data->computer;

            $data->device_code = $cmp->computer_code ?? '-';
            $data->device_name = strtoupper($cmp->computer_name) ?? '-';
            $data->device_sn = $cmp->serial_number ?? '-';
            $data->device_condition = $cmp->condition ?? '-';
            $data->device_asset_ho = $cmp->number_asset_ho ?? '-';
            $data->device_spesifikasi = $cmp->spesifikasi ?? '-';
            $data->device_status_inventory = $cmp->status ?? '-';
            $data->device_note = $cmp->note ?? '-';
            $data->device_pengguna = $cmp->pengguna->username ?? '-';
            $data->device_dept = $cmp->pengguna->department ?? '-';
            $data->device_ip_address = $cmp->ip_address ?? '-';
            $data->device_location = $cmp->location ?? '-';
            $data->device_type = 'Computer';
        } else {
            abort(404, 'Data inspeksi tidak ditemukan');
        }

        if (!empty($data->inspector)) {

            $inspector = array($data->inspector);
        } else {
            $inspector = array('data tidak ada !');
        }


        $crew = User::whereIn('role', ['ict_technician', 'ict_group_leader'])
            ->where('site', $data->site)
            ->pluck('name')
            ->map(fn ($name) => ['name' => $name])
            ->toArray();

        // dd($inspector);
        return Inertia::render('PicaInspeksi/PicaInspeksiEdit', [
            'site' => $data->site,
            'dataInspeksi' => $data,
            'crew' => $crew,
            'inspector' => $inspector
        ]);
    }

    public function update(Request $request)
    {
        $params = $request->all();
        $currentDate = Carbon::now();
        $year = $currentDate->format('Y');

        $data = [
            'findings' => $params['temuan'],
            'findings_action' => $params['tindakan'],
            'due_date' => $params['due_date'],
            'findings_status' => $params['findings_status'],
            'remarks' => $params['remark'],
            'inspector' => $params['inspector'],
            'last_edited_by' => auth()->user()->nrp
        ];

        $dataPica = [
            // 'inspeksi_id' => $request->id,
            'temuan' => $params['temuan'],
            'tindakan' => $params['tindakan'],
            'due_date' => $params['due_date'],
            'remark' => $params['remark'],
            'status_pica' => $params['findings_status'],
            'close_by' => auth()->user()->name,
            'site' => auth()->user()->site,

        ];

        if ($request->file('image_temuan') != null) {
            $document_image = $request->file('image_temuan');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['findings_image'] =  url($new_path_document_image);
            $dataPica['foto_temuan'] =  url($new_path_document_image);
        }

        if ($request->file('image_tindakan') != null) {
            $document_image = $request->file('image_tindakan');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['action_image'] =  url($new_path_document_image);
            $dataPica['foto_tindakan'] =  url($new_path_document_image);
        }

        if ($request->file('image_inspeksi') != null) {
            $document_image = $request->file('image_inspeksi');
            $destinationPath = 'images/';
            $path_document_image = $document_image->store('images', 'public');
            $new_path_document_image = $path_document_image;
            $document_image->move($destinationPath, $new_path_document_image);

            $data['inspection_image'] =  url($new_path_document_image);
        }


        if ($request->device_type === 'Mobile Tower') {
            PicaInspeksi::updateOrCreate(
                ['id' => $request->id], // 🔥 MT pakai ID langsung
                $dataPica
            );
        } else {
            PicaInspeksi::updateOrCreate(
                ['inspeksi_id' => $request->id], // Laptop / PC / Printer
                $dataPica
            );
        }

        // Coba update InspeksiLaptop
        $laptop = InspeksiLaptop::find($request->id);
        $printer = InspeksiPrinter::find($request->id);
        $mt = PicaInspeksi::with('inspeksiMt.mt')->find($request->id);

        if ($laptop) {
            $laptop->update($data);
        } elseif ($printer) {
            $printer->update($data);
        } elseif ($mt && $mt->inspeksiMt && $mt->inspeksiMt->mt) {
            $mt->inspeksiMt->mt->update([
                'inspection_remark'   => $params['remark'],
            ]);
        } else {
            // Jika tidak ada di Laptop, coba update InspeksiComputer
            $computer = InspeksiComputer::find($request->id);

            if ($computer) {
                $computer->update($data);
            } else {
                return back()->with('error', 'Data inspeksi tidak ditemukan.');
            }
        }

        return redirect()->route('picaInspeksi.page', ['site' => $dataPica['site']]);
    }

    // public function detail($id)
    // {
    //     $dataInspeksiLaptop = InspeksiLaptop::find($id);
    //     $dataInspeksiPrinter = InspeksiPrinter::find($id);
    //     $dataInspeksiComputer = InspeksiComputer::find($id);

    //     if ($dataInspeksiLaptop) {
    //         $data = InspeksiLaptop::whereHas('inventory')
    //             ->whereHas('inventory.pengguna')
    //             ->with('inventory.pengguna')
    //             ->where('id', $id)->first();

    //         $data->device_code = $data->inventory->laptop_code ?? '-';
    //         $data->device_name = $data->inventory->laptop_name ?? '-';
    //         $data->device_sn = $data->inventory->serial_number ?? '-';
    //         $data->device_condition = $data->inventory->condition ?? '-';
    //         $data->device_asset_ho = $data->inventory->number_asset_ho ?? '-';
    //         $data->device_spesifikasi = $data->inventory->spesifikasi ?? '-';
    //         $data->device_condition = $data->inventory->condition ?? '-';
    //         $data->device_status_inventory = $data->inventory->status ?? '-';
    //         $data->device_note = $data->inventory->note ?? '-';
    //         $data->device_pengguna = $data->inventory->pengguna->username ?? '-';
    //         $data->device_dept = $data->inventory->pengguna->department ?? '-';
    //         $data->device_ip_address = $data->inventory->ip_address ?? '-';
    //         $data->device_location = $data->inventory->location ?? '-';
    //         $data->device_type = 'Laptop';
    //     } elseif ($dataInspeksiPrinter) {
    //         dd('Masuk printer');
    //         $data = InspeksiComputer::whereHas('computer')
    //             ->whereHas('computer.pengguna')
    //             ->with('computer.pengguna')
    //             ->where('id', $id)->first();

    //         $data->device_code = $data->computer->computer_code ?? '-';
    //         $data->device_name = $data->computer->computer_name ?? '-';
    //         $data->device_sn = $data->computer->serial_number ?? '-';
    //         $data->device_condition = $data->computer->condition ?? '-';
    //         $data->device_asset_ho = $data->computer->number_asset_ho ?? '-';
    //         $data->device_spesifikasi = $data->computer->spesifikasi ?? '-';
    //         $data->device_condition = $data->computer->condition ?? '-';
    //         $data->device_status_inventory = $data->computer->status ?? '-';
    //         $data->device_note = $data->computer->note ?? '-';
    //         $data->device_pengguna = $data->computer->pengguna->username ?? '-';
    //         $data->device_dept = $data->computer->pengguna->department ?? '-';
    //         $data->device_ip_address = $data->computer->ip_address ?? '-';
    //         $data->device_location = $data->computer->location ?? '-';
    //         $data->device_type = 'Computer';
    //     } else {
    //         dd('Masuk Computer');

    //         $data = InspeksiComputer::whereHas('computer')
    //             ->whereHas('computer.pengguna')
    //             ->with('computer.pengguna')
    //             ->where('id', $id)->first();

    //         $data->device_code = $data->computer->computer_code ?? '-';
    //         $data->device_name = $data->computer->computer_name ?? '-';
    //         $data->device_sn = $data->computer->serial_number ?? '-';
    //         $data->device_condition = $data->computer->condition ?? '-';
    //         $data->device_asset_ho = $data->computer->number_asset_ho ?? '-';
    //         $data->device_spesifikasi = $data->computer->spesifikasi ?? '-';
    //         $data->device_condition = $data->computer->condition ?? '-';
    //         $data->device_status_inventory = $data->computer->status ?? '-';
    //         $data->device_note = $data->computer->note ?? '-';
    //         $data->device_pengguna = $data->computer->pengguna->username ?? '-';
    //         $data->device_dept = $data->computer->pengguna->department ?? '-';
    //         $data->device_ip_address = $data->computer->ip_address ?? '-';
    //         $data->device_location = $data->computer->location ?? '-';
    //         $data->device_type = 'Computer';
    //     }

    //     if (!empty($data->inspector)) {

    //         $inspector = array($data->inspector);
    //     } else {
    //         $inspector = array('data tidak ada !');
    //     }
    //     // dd($data);
    //     return Inertia::render('PicaInspeksi/PicaInspeksiDetail', [
    //         'dataInspeksi' => $data,
    //     ]);
    // }

    public function detail($id)
    {
        // $id = '0022c2c0-2b69-4cf7-aece-d4b0c6475f97';
        // $data = InspeksiPrinter::with('printer')->find($id);
        // dd($data);
        if ($data = InspeksiLaptop::with('inventory.pengguna')->find($id)) {

            $inv = $data->inventory;

            $data->device_code = $inv->laptop_code ?? '-';
            $data->device_name = strtoupper($inv->laptop_name) ?? '-';
            $data->device_sn = $inv->serial_number ?? '-';
            $data->device_condition = $inv->condition ?? '-';
            $data->device_asset_ho = $inv->number_asset_ho ?? '-';
            $data->device_spesifikasi = $inv->spesifikasi ?? '-';
            $data->device_status_inventory = $inv->status ?? '-';
            $data->device_note = $inv->note ?? '-';
            $data->device_pengguna = $inv->pengguna->username ?? '-';
            $data->device_dept = $inv->pengguna->department ?? '-';
            $data->device_ip_address = $inv->ip_address ?? '-';
            $data->device_location = $inv->location ?? '-';
            $data->device_type = 'Laptop';
        } elseif ($data = InspeksiPrinter::with('printer')->find($id)) {
            $prt = $data->printer;

            $data->device_code = $prt->printer_code ?? '-';
            $data->device_name = strtoupper($prt->item_name) ?? '-';
            $data->device_sn = $prt->serial_number ?? '-';
            $data->device_condition = $data->condition ?? '-';
            $data->device_asset_ho = $prt->asset_ho_number ?? '-';
            $data->device_spesifikasi = '-';
            $data->device_status_inventory = $prt->status ?? '-';
            $data->device_note = $prt->note ?? '-';
            $data->device_pengguna = $prt->division ?? '-';
            $data->device_dept = $prt->pengguna->department ?? '-';
            $data->device_ip_address = $prt->ip_address ?? '-';
            $data->device_location = $prt->location ?? '-';
            $data->device_type = 'Printer';
        } elseif ($data = PicaInspeksi::with('inspeksiMt.mt')->find($id)) {
            // dd($data);
            $mt = $data->inspeksiMt;

            $data->device_code = $mt->mt->inventory_number ?? '-';
            $data->device_name = strtoupper($mt->mt->mt_code) ?? '-';
            $data->device_sn = '-';
            $data->device_condition = $mt->condition ?? '-';
            $data->device_asset_ho = '-';
            $data->device_spesifikasi = '-';
            $data->device_status_inventory = $mt->mt->status ?? '-';
            $data->device_note = $mt->mt->note ?? '-';
            $data->device_pengguna = '-';
            $data->device_dept = '-';
            $data->device_ip_address = '-';
            $data->device_location = $mt->mt->location ?? '-';
            $data->device_type = 'Mobile Tower';
            $data->findings = $data->temuan;
            $data->findings_image = $data->foto_temuan;
            $data->findings_action = $data->tindakan;
            $data->action_image = $data->foto_tindakan;
            $data->findings_status = $data->status_pica;
            $data->remarks = $data->remark;
            $data->inspector = $mt->pic;
            $data->inspection_image = $mt->inspection_image;
        } elseif ($data = InspeksiComputer::with('computer.pengguna')->find($id)) {

            $cmp = $data->computer;

            $data->device_code = $cmp->computer_code ?? '-';
            $data->device_name = strtoupper($cmp->computer_name) ?? '-';
            $data->device_sn = $cmp->serial_number ?? '-';
            $data->device_condition = $cmp->condition ?? '-';
            $data->device_asset_ho = $cmp->number_asset_ho ?? '-';
            $data->device_spesifikasi = $cmp->spesifikasi ?? '-';
            $data->device_status_inventory = $cmp->status ?? '-';
            $data->device_note = $cmp->note ?? '-';
            $data->device_pengguna = $cmp->pengguna->username ?? '-';
            $data->device_dept = $cmp->pengguna->department ?? '-';
            $data->device_ip_address = $cmp->ip_address ?? '-';
            $data->device_location = $cmp->location ?? '-';
            $data->device_type = 'Computer';
        } else {
            abort(404, 'Data inspeksi tidak ditemukan');
        }

        return Inertia::render('PicaInspeksi/PicaInspeksiDetail', [
            'dataInspeksi' => $data,
        ]);
    }

    public function exportPdf(Request $request)
    {
        // dd($request);
        // 1. Validasi input
        $validated = $request->validate([
            'device' => 'required|in:Laptop,Computer',
            'site' => 'required|string',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'pic' => 'nullable|string',
        ]);
        if ($request->device == 'Computer') {
            $devicePage = 'COMPUTER';
        } else {
            $devicePage = 'LAPTOP';
        }
        // 2. Ambil data dari request
        $site = $validated['site'];
        $device = $validated['device'];
        $startDate = $validated['startDate'] ?? null;
        $endDate = $validated['endDate'] ?? null;
        $picName = $validated['pic'] ?? null;

        $startDateConv = $startDate ? Carbon::parse($startDate)->translatedFormat('d F Y') : null;
        $endDateConv = $endDate ? Carbon::parse($endDate)->translatedFormat('d F Y') : null;

        // 3. Ambil data inspeksi berdasarkan device
        $dataPica = $device === 'Computer'
            ? $this->getComputerInspections($site, $startDate, $endDate)
            : $this->getLaptopInspections($site, $startDate, $endDate);

        // 4. Bersihkan path gambar agar lokal dan bisa dibaca oleh DomPDF
        foreach ($dataPica as $item) {
            if ($item->findings_image) {
                $filename = Str::after($item->findings_image, '/images/');
                $item->findings_image = $filename;
            }

            if ($item->action_image) {
                $filename = Str::after($item->action_image, '/images/');
                $item->action_image = $filename;
            }
            // dd($item->findings_image);
        }

        // 5. Cari data yang berstatus "Y" untuk mendapatkan PIC approval
        $inspectionY = $dataPica->firstWhere('inspection_status', 'Y');
        $picApproved = $inspectionY->approved_by ?? null;
        $qr_base64Approved = $picApproved ? $this->generateQrFromUserName($picApproved) : null;

        // 6. Generate QR untuk PIC inspeksi (yang dipilih manual)
        $qr_base64Pic = $picName ? $this->generateQrFromUserName($picName) : null;

        // 7. Generate PDF
        Pdf::setOptions(['isRemoteEnabled' => true]); // <--- WAJIB agar gambar URL bisa muncu

        // 8. Return PDF response
        if ($startDate && $endDate) {
            $pdf = Pdf::loadView('itportal.rekapAllInspeksi.rekapPica', compact(
                'dataPica',
                'site',
                'picName',
                'picApproved',
                'qr_base64Approved',
                'qr_base64Pic',
                'startDateConv',
                'endDateConv',
                'devicePage',
            ))->setPaper('A4', 'landscape');
            return $pdf->stream('Pica-inspection-' . strtolower($device) . '-report-periode' . $startDate . '-' . $endDate . 'pdf');
        } else {
            $year = Carbon::now()->year;
            $pdf = Pdf::loadView('itportal.rekapAllInspeksi.rekapPica', compact(
                'dataPica',
                'site',
                'picName',
                'picApproved',
                'qr_base64Approved',
                'qr_base64Pic',
                'year',
                'devicePage',
            ))->setPaper('A4', 'landscape');
            return $pdf->stream('Pica-inspection-' . strtolower($device) . '-report-periode' . $year . 'pdf');
        }
    }

    private function getComputerInspections($site, $startDate, $endDate)
    {
        $query = InspeksiComputer::with('computer.pengguna')
            ->where('site', $site)
            ->whereNotNull('findings_image');

        if ($startDate && $endDate) {
            $query->whereBetween('created_date', [$startDate, $endDate]);
        } else {
            $query->where('year', Carbon::now()->year);
        }

        return $query->get()->map(function ($item) {
            $spesifikasi = $item->computer->spesifikasi ?? '';
            $item->spesifikasi_singkat = $spesifikasi !== '' ? Str::before($spesifikasi, ',') : '-';
            $item->no_inv = $item->computer->computer_code ?? '-';
            $item->loc = $item->computer->location ?? '-';
            return $item;
        });
    }

    private function getLaptopInspections($site, $startDate, $endDate)
    {
        $query = InspeksiLaptop::with('inventory.pengguna')
            ->where('site', $site)
            ->whereNotNull('findings_image');

        if ($startDate && $endDate) {
            $query->whereBetween('created_date', [$startDate, $endDate]);
        } else {
            $query->where('year', Carbon::now()->year);
        }

        return $query->get()->map(function ($item) {
            $spesifikasi = $item->inventory->spesifikasi ?? '';
            $item->spesifikasi_singkat = $spesifikasi !== '' ? Str::before($spesifikasi, ',') : '-';
            $item->no_inv = $item->inventory->laptop_code ?? '-';
            $item->loc = $item->inventory->location ?? '-';
            return $item;
        });
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
