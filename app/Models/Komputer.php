<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komputer extends Model
{
    use HasFactory;

    protected $fillable = [
        'computer_code',
        'computer_name',
        'number_asset_ho',
        'assets_category',
        'spesifikasi',
        'serial_number',
        'aplikasi',
    ];
}