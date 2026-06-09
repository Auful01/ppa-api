<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleComputer extends Model
{
    protected $table = 'schedule_computer';

    protected $fillable = [
        'id_computer',
        'tanggal_inspection',
        'actual_inspection',
        'bulan',
        'tahun',
        'computer_code',
        'dept',
        'site',
    ];

    public $timestamps = true;

    public function laptop()
    {
        return $this->belongsTo(InvComputer::class, 'id_computer', 'id');
    }
}

