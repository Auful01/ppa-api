<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleLaptop extends Model
{
    protected $table = 'schedule_laptop';

    protected $fillable = [
        'id_laptop',
        'tanggal_inspection',
        'actual_inspection',
        'bulan',
        'tahun',
        'laptop_code',
        'dept',
        'site',
    ];

    public $timestamps = true;

    public function laptop()
    {
        return $this->belongsTo(InvLaptop::class, 'id_laptop', 'id');
    }
}

