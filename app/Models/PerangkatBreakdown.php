<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerangkatBreakdown extends Model
{
    use HasFactory;
    protected $fillable = [
        'device_category',
        'category_breakdown',
        'id_report',
        'inventory_number',
        'id_perangkat',
        'device_name',
        'start_time',
        'end_time',
        'created_date',
        'month',
        'year',
        'location',
        'root_cause',
        'root_cause_category',
        'garansion_laptop_code',
        'status',
        'pic',
        'site',
    ];
}
