<?php

namespace App\Imports;

use App\Models\InvWirelless;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class WirellessImport implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function startRow(): int
    {
        return 17; // Mulai dari baris kedua
    }

    public $duplicateRecords = [];

    public function model(array $row)
    {
        $row = array_slice($row, 0, 15);

        $emptyCheck = array_filter(array_slice($row, 1, 3)); 
        if (count($emptyCheck) === 0) {
            return null; // Abaikan jika semua kolom utama kosong
        }
        
        $inventoryNumber = trim($row[3]);

        if ($inventoryNumber === '' || $inventoryNumber === '-' || $inventoryNumber === null) {
            $existingDataInvNumber = null;
        } else {
            $existingDataInvNumber = InvWirelless::where('inventory_number', $inventoryNumber)->where('site', auth()->user()->site)->first();
        }

        $maxId = InvWirelless::max('max_id');
        if (is_null($maxId)) {
            $maxId = 1;
        }else{
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

        $tanggal = $row[14];
        if ($tanggal === '' || $tanggal === '-' || $tanggal === null) {
            $tanggal = null; // Biarkan lanjut tanpa mendeteksi duplikasi
        } else {
            $tanggal = $this->convertToDate($row[14]);
        }

        return new InvWirelless([
            'max_id' => $maxId,
            'device_name' => $row[1],
            'asset_ho_number' => $row[2],
            'inventory_number' => $row[3],
            'serial_number' => $row[4],
            'frequency' => $row[5],
            'ip_address' => $row[6],
            'mac_address' => $row[7],
            'device_brand' => $row[8],
            'device_type' => $row[9],
            'device_model' => $row[10],
            'location' => $row[11],
            'status' => $row[12],
            'date_of_inventory' => $tanggal,
            'note' => $row[13],
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
