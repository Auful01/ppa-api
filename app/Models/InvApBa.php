<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvApBa extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'max_id',
        'device_name',
        'inventory_number',
        'asset_ho_number',
        'serial_number',
        'frequency',
        'mac_address',
        'ip_address',
        'device_brand',
        'device_type',
        'device_model',
        'location',
        'status',
        'note',
        'site'
    ];
}
