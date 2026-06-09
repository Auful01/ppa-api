<?php

namespace App\Imports;

use App\Models\UserAll;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UserPenggunaImport implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function startRow(): int
    {
        return 12; // Mulai dari baris kedua
    }

    public function model(array $row)
    {
        $row = array_slice($row, 0, 7);

        $emptyCheck = array_filter(array_slice($row, 1, 3));
        if (count($emptyCheck) === 0) {
            return null; // Abaikan jika semua kolom utama kosong
        }

        if (!isset($row[1]) || empty($row[1])) {
            return null; // Skip jika tidak ada data
        }


        // Ambil nilai NRP dari data row
        $nrp = strtoupper($row[1]);

        // Periksa apakah NRP sudah ada di database
        $existingUser = UserAll::where('nrp', $nrp)->exists();

        // Jika NRP sudah ada, lewati proses penyimpanan
        if ($existingUser) {
            return null; // Atau logik lain jika diperlukan
        }

        // return dd($row);
        return new UserAll([
            'nrp' =>  $nrp,
            'username' =>  strtoupper($row[2]),
            'department' =>  strtoupper($row[3]),
            'position' =>  strtoupper($row[4]),
            'site' =>  strtoupper($row[5]),
            'email' =>  strtoupper($row[6]),
        ]);
    }

    // public function model(array $row)
    // {
    //     $row = array_slice($row, 0, 7);

    //     // Abaikan baris jika kolom utama kosong (misal kolom 1-3)
    //     $emptyCheck = array_filter(array_slice($row, 1, 3));
    //     if (count($emptyCheck) === 0 || !isset($row[1]) || empty($row[1])) {
    //         return null;
    //     }

    //     $nrp = strtoupper($row[1]);

    //     // Gunakan firstOrNew untuk update jika ada, atau buat baru jika tidak
    //     $user = UserAll::firstOrNew(['nrp' => $nrp]);

    //     $user->username   = strtoupper($row[2] ?? '');
    //     $user->department = strtoupper($row[3] ?? '');
    //     $user->position   = strtoupper($row[4] ?? '');
    //     $user->site       = strtoupper($row[5] ?? '');
    //     $user->email      = strtoupper($row[6] ?? '');

    //     $user->save(); // Simpan perubahan (baik insert atau update)

    //     return null; // Tidak perlu return model karena sudah disimpan manual
    // }
}
