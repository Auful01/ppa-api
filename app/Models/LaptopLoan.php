<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaptopLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_number',
        'asset_number',
        'serial_number',
        'laptop_name',
        'laptop_brand',
        'spesifikasi',
        'laptop_model',
        'processor',
        'hdd',
        'ssd',
        'ram',
        'vga',
        'color',
        'os',
        'application',
        'ip_address',
        'laptop_condition',
        'laptop_status',
        'last_borrower',
    ];
}
