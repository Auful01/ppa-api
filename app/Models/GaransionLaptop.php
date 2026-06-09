<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GaransionLaptop extends Model
{
    use HasFactory;

    protected $fillable = [
        'garansion_code',
        'laptop_id',
        'inventory_number',
        'status',
        'start_progress',
        'end_progress',
        'month',
        'year',
        'date_of_garansion',
        'record_data',
        'hardware_damage',
    ];
}
