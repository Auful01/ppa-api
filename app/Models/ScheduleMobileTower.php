<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ScheduleMobileTower extends Model
{
    use HasFactory;

    protected $table = 'schedule_mobile_tower';

    protected $fillable = [
        'id_mobile_tower',
        'tanggal_inspection',
        'actual_inspection',
        'bulan',
        'tahun',
        'mobile_tower_code',
        'dept',
        'site',
    ];

    protected $casts = [
        'tanggal_inspection' => 'date',
        'actual_inspection' => 'date',
    ];

    public $timestamps = true;

    public function inventory()
    {
        return $this->belongsTo(InvMobileTower::class, 'id_mobile_tower', 'id');
    }
}
