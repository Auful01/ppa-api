<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvNvr extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'nvr_code',
        'nvr_name',
        'nvr_brand',
        'type_nvr',
        'number_of_channel',
        'mac_address',
        'ip_address',
        'location',
        'channel_free',
        'remarks',
        'status',
        'last_status_ping',
        'last_update_ping',
        'details',
    ];
}
