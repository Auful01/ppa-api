<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class kpiVhms extends Model
{
    use HasFactory;
    protected $fillable = [
        'week_data',
        'unit_code',
        'remark',
        'month',
        'year',
        'status',
        'pic',
        'created_by',
    ];
}
