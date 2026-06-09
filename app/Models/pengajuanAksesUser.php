<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class pengajuanAksesUser extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_akses_user';  // The name of your table

    // Specify the primary key (if it's not the default 'id')
    protected $primaryKey = 'id';  // (optional, if it's not 'id')

    // Optionally, specify the columns you want to mass-assign
    protected $fillable = ['id_user', 'nrp_user', 'nama_user','divisi','dept','site','status'];
}
