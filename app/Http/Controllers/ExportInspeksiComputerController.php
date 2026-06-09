<?php

namespace App\Http\Controllers;

use App\Models\InspeksiComputer;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ExportInspeksiComputerController extends Controller
{
    public function exportPdfAll(Request $request)
    {
        $pic = $request->pic;
        $site = $request->site;

        $thisTriwulan = $request->triwulan ?? Carbon::now()->quarter;


        $thisYearEncrypt = $request->query('year') ?? Carbon::now()->year;
        try {
            $thisYear = Crypt::decryptString($thisYearEncrypt);
        } catch (\Exception $e) {
            abort(403, "Akses tidak valid");
        }

        if ($thisYear > 2500) {
            return back()->with('error', 'Tahun tidak terdeteksi.');
        }

        $inspeksiComputerAll = InspeksiComputer::with('computer.pengguna')
            ->where('site', $site)
            ->where('triwulan', $thisTriwulan)
            ->where('year', $thisYear)
            ->get();

        $unitScrap = $inspeksiComputerAll->where('inventory_status', 'SCRAP')->count();
        $unitUtilize = $inspeksiComputerAll->where('inventory_status', '!=', 'SCRAP')->count();


        // Ambil 1 data inspeksi yang statusnya Y
        $inspectionY = $inspeksiComputerAll->firstWhere('inspection_status', 'Y');
        if ($inspectionY) {
            $picApproved = $inspectionY->approved_by;
        } else {
            $picApproved = '';
        }
        $qr_base64Approved = null;
        if ($inspectionY && $picApproved) {
            $approvedUser = User::where('name', $picApproved)->first();
            if ($approvedUser) {
                $qrString = "NRP: {$approvedUser->nrp}, Nama: {$approvedUser->name}, Jabatan: {$approvedUser->position}";
                $barcode = new \Milon\Barcode\DNS2D();
                $barcode->setStorPath(storage_path('framework/barcodes/'));
                $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');
                $qr_base64Approved = 'data:image/png;base64,' . $pngData;
            }
        }

        $qr_base64Pic = null;
        $picInspeksi = User::where('name', $pic)->first();
        if ($picInspeksi) {
            $qrString = "NRP: {$picInspeksi->nrp}, Nama: {$picInspeksi->name}, Jabatan: {$picInspeksi->position}";
            $barcode = new \Milon\Barcode\DNS2D();
            $barcode->setStorPath(storage_path('framework/barcodes/'));
            $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');
            $qr_base64Pic = 'data:image/png;base64,' . $pngData;
        }
        // Load view sesuai site
        $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiComputerAll', compact(
            'inspeksiComputerAll',
            'thisYear',
            'thisTriwulan',
            'unitScrap',
            'unitUtilize',
            'site',
            'pic',
            'picApproved',
            'qr_base64Approved',
            'qr_base64Pic'
        ))->setPaper('A4', 'landscape');

        return $pdf->download('inspection-computer-report-periode-triwulan-' . $thisTriwulan . '-' . $thisYear . '.pdf');
    }

    public function exportPdfSingle(Request $request)
    {
        $user = Auth::user();
        $site = $user->site;
        $thisYear = Carbon::now()->year;

        // Ambil hanya satu data inspeksi yang sesuai dan sudah disetujui
        $inspection = InspeksiComputer::with('computer.pengguna')
            ->where('id', $request->inspeksiId)
            // ->where('inspection_status', 'Y')
            ->first();

        if (!$inspection) {
            return back()->with('error', 'Data inspeksi tidak ditemukan atau belum disetujui.');
        }

        // Ambil data user yang menyetujui
        if ($inspection->inspection_status == 'Y') {
            $approvedUser = User::where('name', $inspection->approved_by)->first();
            $inspectorUser = User::where('name', $inspection->inspector)->first();
        } else {
            $approvedUser = '';
            $inspectorUser = '';
        }

        // Tambahkan data tambahan untuk QR Code
        if ($approvedUser) {
            $qrString = "NRP: {$approvedUser->nrp}, Nama: {$approvedUser->name}, Jabatan: {$approvedUser->position}";
            $inspection->qr_string = $qrString;
            $barcode = new \Milon\Barcode\DNS2D();
            $barcode->setStorPath(storage_path('framework/barcodes/')); // agar tidak error
            $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');

            $inspection->qr_base64Approved = 'data:image/png;base64,' . $pngData;
        } else {
            $inspection->qr_string = null;
            $inspection->qr_base64Approved = null;
        }

        if ($inspectorUser) {
            $qrString = "NRP: {$inspectorUser->nrp}, Nama: {$inspectorUser->name}, Jabatan: {$inspectorUser->position}";
            $inspection->qr_string = $qrString;
            $barcode = new \Milon\Barcode\DNS2D();
            $barcode->setStorPath(storage_path('framework/barcodes/')); // agar tidak error
            $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');

            $inspection->qr_base64Inspector = 'data:image/png;base64,' . $pngData;
        } else {
            $inspection->qr_string = null;
            $inspection->qr_base64Inspector = null;
        }

        if ($site == 'ADW') {
            $thisMonth = $inspection->triwulan;
            $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiComputer', [
                'inspection' => $inspection,
                'thisYear' => $thisYear,
                'thisMonth' => $thisMonth,
                'site' => $site,
            ])->setPaper('A4', 'portrait');
            return $pdf->download('inspection-computer-report-periode-' . 'bulan-' . $thisMonth . 'tahun-' . $thisYear . '.pdf');
        } else {
            $thisTriwulan = $inspection->triwulan;
            $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiComputer', [
                'inspection' => $inspection,
                'thisYear' => $thisYear,
                'thisTriwulan' => $thisTriwulan,
                'site' => $site,
            ])->setPaper('A4', 'portrait');
            return $pdf->download('inspection-computer-report-periode-' . 'triwulan-' . $thisTriwulan . 'tahun-' . $thisYear . '.pdf');
        }
    }
}
