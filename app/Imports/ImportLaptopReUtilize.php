<?php

namespace App\Imports;

use App\Models\InvLaptopReUtilize;
use Maatwebsite\Excel\Concerns\ToModel;

class ImportLaptopReUtilize implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function startRow(): int
    {
        return 17;  
    }

    public function model(array $row)
    {
        return new InvLaptopReUtilize([
            'max_id' => $row[0],
            'device_name' => $row[1],
            'inventory_number' => $row[2],
            'serial_number' => $row[3],
            'ip_address' => $row[4],
            'mac_address' => $row[5],
            'device_brand' => $row[6],
            'device_type' => $row[7],
            'device_model' => $row[8],
            'location' => $row[9],
            'status' => $row[10],
            'note' => $row[11],
        ]);
    }
}
