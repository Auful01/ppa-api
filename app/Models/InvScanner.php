<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Guid\Guid;
use Ramsey\Uuid\Guid\GuidInterface;
use Ramsey\Uuid\Guid\Guid\Guid7;

class InvScanner extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'max_id',
        'item_name',
        'scanner_code',
        'asset_ho_number',
        'serial_number',
        'ip_address',
        'mac_address',
        'scanner_brand',
        'scanner_type',
        'division',
        'department',
        'location',
        'date_of_inventory',
        'status',
        'note',
        'site'
    ];
}
