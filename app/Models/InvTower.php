<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvTower extends Model
{
    use HasFactory;

    protected $fillable = [
        'tower_code',
        'location',
        'detail_location',
        'tower_type',
        'status',
        'note',
    ];
}
