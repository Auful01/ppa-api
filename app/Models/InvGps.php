<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvGps extends Model
{
    use HasFactory;

    protected $fillable = [
        'gps_code',
        'location',
        'brand_gps',
        'model_gps',
        'type_gps',
        'phone_number',
        'serial_number',
        'date_of_inventory',
        'status',
        'note',
    ];
}
