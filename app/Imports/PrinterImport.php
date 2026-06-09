<?php

namespace App\Imports;

use App\Models\InvPrinter;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

class PrinterImport implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    // public function startCell(): string
    // {
    //     return 'B3';
    // }

    public function startRow(): int
    {
        return 17;
    }

    public $duplicateRecords = [];

    public function model(array $row)
    {

        $row = array_slice($row, 0, 15);

        $emptyCheck = array_filter(array_slice($row, 1, 3)); 
        if (count($emptyCheck) === 0) {
            return null; // Abaikan jika semua kolom utama kosong
        }

        $inventoryNumber = trim($row[3]); // Hilangkan spasi di awal dan akhir

        // Cek apakah SN kosong, hanya tanda hubung, atau hanya spasi
        if ($inventoryNumber === '' || $inventoryNumber === '-' || $inventoryNumber === null) {
            $existingDataInvNumber = null; // Biarkan lanjut tanpa mendeteksi duplikasi
        } else {
            $existingDataInvNumber = InvPrinter::where('printer_code', $inventoryNumber)->where('site', auth()->user()->site)->first();
        }

        $tanggal = $row[14];
        if ($tanggal === '' || $tanggal === '-' || $tanggal === null) {
            $tanggal = null; // Biarkan lanjut tanpa mendeteksi duplikasi
        } else {
            $tanggal = $this->convertToDate($row[14]);
        }

        $maxId = InvPrinter::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        } else {
            $maxId = $maxId + 1;
        }


        if ($existingDataInvNumber) {
            $this->duplicateRecords[] = [
                'inventory_number' => $existingDataInvNumber->inventory_number,
                'location' => $existingDataInvNumber->location,
                'site' => $existingDataInvNumber->site,
            ];
            return null;
        }

        return new InvPrinter([
            'max_id' => $maxId,
            'item_name' => $row[1],
            'asset_ho_number' => $row[2],
            'printer_code' => $row[3],
            'serial_number' => $row[4],
            'ip_address' => $row[5],
            'mac_address' => $row[6],
            'printer_brand' => $row[7],
            'printer_type' => $row[8],
            'division' => $row[9],
            'department' => $row[10],
            'location' => $row[11],
            'status' => strtoupper($row[12]),
            'note' => $row[13],
            'date_of_inventory' => $tanggal,
            'site' => auth()->user()->site
        ]);
    }

    public function getDuplicateRecords()
    {
        return $this->duplicateRecords;
    }

    private function convertToDate($value)
    {
        try {
            $value = trim($value);
    
            // Jika format serial Excel (numeric)
            if (is_numeric($value)) {
                return \Carbon\Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)
                );
            }
    
            // Jika format tanggal string 'd/m/Y'
            return \Carbon\Carbon::createFromFormat('d/m/Y', $value);
        } catch (\Exception $e) {
            \Log::info('Gagal parsing tanggal: ' . $value . ' | Error: ' . $e->getMessage());
            return null;
        }
    }
}