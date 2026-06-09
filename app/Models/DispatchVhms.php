<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispatchVhms extends Model
{
    use HasFactory;
    protected $fillable = [
        'unit_code',
        'unit_model',
        'sn_unit',
        'ip_unit',
        'wirelless_type',
        'mac_wirelless',
        'status',
        'remark',
        'bulan',
        'tahun',
    ];
}
