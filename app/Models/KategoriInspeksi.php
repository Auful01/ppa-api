<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriInspeksi extends Model
{
    protected $table = 'kategori_inspeksi';
    protected $primaryKey = 'id_kat_inspeksi';
    public $timestamps = false; // because your table has no created_at/updated_at

    protected $fillable = [
        'kategori_inspeksi',
        'nama_judul',
        'urutan',
        'parent',
        'status',
    ];
}
