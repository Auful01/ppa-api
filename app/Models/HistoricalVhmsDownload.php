<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricalVhmsDownload extends Model
{
    protected $table = 'historical_vhms_downloads';

    protected $fillable = [
        'sn',
        'cn',
        'model',
        'status',
        'last_download',
        'last_operation',
        'pld_last_record',
        'trend_last_record',
        'fault_last_record',
        'his_last_record',
        'last_histori',
        'date',
        'month',
        'year',
        'site',
    ];
}
