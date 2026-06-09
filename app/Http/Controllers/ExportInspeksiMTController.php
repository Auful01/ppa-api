<?php

namespace App\Http\Controllers;

use App\Models\InspeksiMobileTower;
use App\Models\KategoriInspeksi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
// use Milon\Barcode\DNS2D;
use DNS2D;

class ExportInspeksiMTController extends Controller
{
    public function exportPdfAll(Request $request)
    {
        $user = Auth::user();
        try {
            $site = Crypt::decryptString($request->site);
        } catch (\Exception $e) {
            $site = $user->site;
        }
        Carbon::setLocale('id');
        $thisMonth = $request->month ?? Carbon::now()->month;
        $thisYear = $request->year ?? Carbon::now()->year;
        // dd($thisMonth);

        $thisMonthTeks = Carbon::create()->month((int) $thisMonth)->translatedFormat('F');

        if ($thisYear > 2500) {
            return back()->with('error', 'Tahun tidak terdeteksi.');
        }

        $inspections = InspeksiMobileTower::with('mt')
            ->where('month', $thisMonth)
            ->where('year', $thisYear)
            ->where('site', $site)
            ->where('inspection_status', 'Y')
            ->get();

        // dd($inspection);

        if (!$inspections) {
            return back()->with('error', 'Data inspeksi tidak ditemukan atau belum disetujui.');
        }

        foreach ($inspections as $inspection) {
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

        }

        $dataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')
            ->where('parent', 0)
            ->orderBy('urutan', 'ASC')
            ->get();

        $subDataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')
            ->where('parent', '!=', 0)
            ->orderBy('urutan', 'ASC')
            ->get();

        // return view('itportal.rekapAllInspeksi.inspeksiMobileTower', [
        //     'inspection' => $inspection,
        //     'thisYear' => $thisYear,
        //     'thisMonth' => $thisMonth,
        //     'thisMonthTeks' => $thisMonthTeks,
        //     'site' => $site,
        //     'dataKategori' => $dataKategori,
        //     'subDataKategori' => $subDataKategori,
        // ]);

        // dd($inspection);

        // Kirim ke view
        $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiMobileTowerAll', [
            'inspections' => $inspections,
            'thisYear' => $thisYear,
            'thisMonth' => $thisMonth,
            'thisMonthTeks' => $thisMonthTeks,
            'site' => $site,
            'dataKategori' => $dataKategori,
            'subDataKategori' => $subDataKategori,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('inspection-MobileTower-report-periode-' . $thisMonthTeks . '-' . $thisYear . '.pdf');
    }

    public function exportPdfSingle(Request $request)
    {
        $user = Auth::user();
        $site = $user->site;
        Carbon::setLocale('id');
        $thisMonth = Carbon::now()->month;
        $thisMonthTeks = Carbon::now()->translatedFormat('F');
        $thisYear = Carbon::now()->year;

        if ($thisYear > 2500) {
            return back()->with('error', 'Tahun tidak terdeteksi.');
        }

        $inspection = InspeksiMobileTower::with('mt')
            ->where('id', $request->inspeksiId)
            // ->where('inspection_status', 'Y')
            ->first();

        // dd($inspection);

        if (!$inspection) {
            return back()->with('error', 'Data inspeksi tidak ditemukan atau belum disetujui.');
        }

        $nomor_mt = $inspection->mt->inventory_number;

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

        $dataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')
            ->where('parent', 0)
            ->orderBy('urutan', 'ASC')
            ->get();

        $subDataKategori = KategoriInspeksi::where('kategori_inspeksi', 'MT')
            ->where('parent', '!=', 0)
            ->orderBy('urutan', 'ASC')
            ->get();

        // return view('itportal.rekapAllInspeksi.inspeksiMobileTower', [
        //     'inspection' => $inspection,
        //     'thisYear' => $thisYear,
        //     'thisMonth' => $thisMonth,
        //     'thisMonthTeks' => $thisMonthTeks,
        //     'site' => $site,
        //     'dataKategori' => $dataKategori,
        //     'subDataKategori' => $subDataKategori,
        // ]);

        // Kirim ke view
        $pdf = Pdf::loadView('itportal.rekapAllInspeksi.inspeksiMobileTower', [
            'inspection' => $inspection,
            'thisYear' => $thisYear,
            'thisMonth' => $thisMonth,
            'thisMonthTeks' => $thisMonthTeks,
            'site' => $site,
            'dataKategori' => $dataKategori,
            'subDataKategori' => $subDataKategori,
        ])->setPaper('A4', 'portrait');

        return $pdf->download('inspection-' . $nomor_mt . '-report-periode-' . $thisMonthTeks . '-' . $thisYear . '.pdf');
    }
}
