<?php

namespace App\Http\Controllers;

use App\Models\Aduan;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExportAduanAllSiteController extends Controller
{
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
            return $pdf->download('Data-aduan-' . '-report-periode' . $startDate . '-' . $endDate . '.pdf');
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
            return $pdf->download('Data-aduan-' . '-report-periode' . $year . '.pdf');
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
