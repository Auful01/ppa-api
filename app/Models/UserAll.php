<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nrp',
        'username',
        'department',
        'position',
        'email',
        'site',
    ];
}
