<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvServer extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'server_name',
        'server_brand',
        'type_server',
        'category_server',
        'ip_address',
        'category_server',
        'number_asset_ho',
        'service_tag',
        'cpu',
        'ram',
        'hdd',
        'os',
        'status',
        'remark',
    ];
}
