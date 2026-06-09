<?php

namespace App\Http\Controllers;

use App\Models\InspeksiPrinter;
use App\Models\InvPrinter;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
// use Milon\Barcode\DNS2D;
use DNS2D;

class ExportInspeksiPrinterController extends Controller
{
    public function exportPdfAll(Request $request)
    {

        $user = Auth::user();

        $pic = $request->pic;
        // dd($pic);

        try {
            $site = Crypt::decryptString($request->site);
        } catch (\Exception $e) {
            $site = $user->site;
        }
        Carbon::setLocale('id');
        $thisMonth = $request->month ?? Carbon::now()->month;
        $thisYear = $request->year ?? Carbon::now()->year;

        $thisMonthTeks = Carbon::create()->month((int) $thisMonth)->translatedFormat('F');
        // dd($thisMonth);

        if ($thisYear > 2500) {
            return back()->with('error', 'Tahun tidak terdeteksi.');
        }
        // dd($request->site);
        $inspeksiPrinterAll = InspeksiPrinter::with('printer')
            ->where('site', $site)
            ->where('year', $thisYear)
            ->where('month', $thisMonth)
            ->orderBy(
                InvPrinter::select('printer_code')
                    ->whereColumn('inv_printers.id', 'inspeksi_printers.inv_printer_id')
            )
            ->get();

        $unitScrap = InspeksiPrinter::with('printer')
            ->where('site', $site)
            ->where('year', $thisYear)
            ->where('month', $thisMonth)
            ->where('inventory_status', 'SCRAP')
            ->orderBy(
                InvPrinter::select('printer_code')
                    ->whereColumn('inv_printers.id', 'inspeksi_printers.inv_printer_id')
            )
            ->count();

        $unitUtilize = InspeksiPrinter::with('printer')
            ->where('site', $site)
            ->where('year', $thisYear)
            ->where('month', $thisMonth)
            ->where('inventory_status', '!=', 'SCRAP')
            ->orderBy(
                InvPrinter::select('printer_code')
                    ->whereColumn('inv_printers.id', 'inspeksi_printers.inv_printer_id')
            )
            ->count();


        $inspectionY = $inspeksiPrinterAll->firstWhere('inspection_status', 'Y');
        // if ($inspectionY) {
        // if ($user->site == 'HO') {
        $picApproved = 'EDI NUGROHO';
        //     }else{
        //         $picApproved = $inspectionY->approved_by;
        //     }
        // } else {
        //     $picApproved = '';
        // }
        $qr_base64Approved = null;
        // if ($inspectionY && $picApproved) {
        $approvedUser = User::where('name', $picApproved)->first();
        if ($approvedUser) {
            $qrString = "NRP: {$approvedUser->nrp}, Nama: {$approvedUser->name}, Jabatan: {$approvedUser->position}";
            $barcode = new \Milon\Barcode\DNS2D();
            $barcode->setStorPath(storage_path('framework/barcodes/'));
            $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');
            $qr_base64Approved = 'data:image/png;base64,' . $pngData;
        }
        // }

        $qr_base64Pic = null;
        $picInspeksi = User::where('name', $pic)->first();
        if ($picInspeksi) {
            $qrString = "NRP: {$picInspeksi->nrp}, Nama: {$picInspeksi->name}, Jabatan: {$picInspeksi->position}";
            $barcode = new \Milon\Barcode\DNS2D();
            $barcode->setStorPath(storage_path('framework/barcodes/'));
            $pngData = $barcode->getBarcodePNG($qrString, 'QRCODE');
            $qr_base64Pic = 'data:image/png;base64,' . $pngData;
        }

        $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiPrinterAll', compact('inspeksiPrinterAll', 'thisYear', 'thisMonthTeks', 'thisMonth', 'unitScrap', 'unitUtilize', 'site', 'pic', 'picApproved', 'qr_base64Approved', 'qr_base64Pic'))
            ->setPaper('A4', 'landscape');


        return $pdf->download('inspection-printer-report-periode-' . $thisMonthTeks . '-' . $thisYear . '.pdf');
    }

    public function exportPdfSingle(Request $request)
    {
        $user = Auth::user();
        try {
            $site = Crypt::decryptString($request->site);
        } catch (\Exception $e) {
            $site = $user->site;
        }
        Carbon::setLocale('id');
        $thisMonth = Carbon::now()->month;
        $thisMonthTeks = Carbon::now()->translatedFormat('F');
        $thisYear = Carbon::now()->year;

        if ($thisYear > 2500) {
            return back()->with('error', 'Tahun tidak terdeteksi.');
        }

        // Ambil hanya satu data inspeksi yang sesuai dan sudah disetujui
        $inspection = InspeksiPrinter::with('printer')->where('id', $request->inspeksiId)->first();

        if (!$inspection) {
            return back()->with('error', 'Data inspeksi tidak ditemukan atau belum disetujui.');
        }

        $nomor_printer = $inspection->printer->printer_code;

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

        // return view('itportal.rekapAllInspeksi.inspeksiPrinter', [
        //     'inspection' => $inspection,
        //     'thisYear' => $thisYear,
        //     'thisMonth' => $thisMonth,
        //     'thisMonthTeks' => $thisMonthTeks,
        //     'site' => $site,
        // ]);

        // Kirim ke view
        $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiPrinter', [
            'inspection' => $inspection,
            'thisYear' => $thisYear,
            'thisMonth' => $thisMonth,
            'thisMonthTeks' => $thisMonthTeks,
            'site' => $site,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('inspection-' . $nomor_printer . '-report-periode-' . $thisMonthTeks . '-' . $thisYear . '.pdf');
    }
}
