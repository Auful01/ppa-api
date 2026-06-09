<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvChannelRadio extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'channel_name',
        'rx',
        'tx',
        'note',
        'isr_id',
        'status_isr',
        'maintenance_by',
        'status',
        'remark',
    ];
}
