<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComputerLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_number',
        'asset_number',
        'serial_number',
        'computer_name',
        'computer_brand',
        'spesifikasi',
        'computer_model',
        'processor',
        'hdd',
        'ssd',
        'ram',
        'vga',
        'color',
        'os',
        'application',
        'ip_address',
        'computer_condition',
        'computer_status',
        'last_borrower',
    ];
}
