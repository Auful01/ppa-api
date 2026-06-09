<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SchedulePrinter extends Model
{
    use HasFactory;

    protected $table = 'schedule_printer';

    protected $fillable = [
        'id_printer',
        'tanggal_inspection',
        'actual_inspection',
        'bulan',
        'tahun',
        'printer_code',
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
        return $this->belongsTo(InvPrinter::class, 'id_printer', 'id');
    }
}
