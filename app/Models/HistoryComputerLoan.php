<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryComputerLoan extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_number',
        'asset_number',
        'nik',
        'name',
        'position',
        'department',
        'date_of_loan',
        'date_of_return',
        'remark',
        'link',
        'pic',
    ];
}
