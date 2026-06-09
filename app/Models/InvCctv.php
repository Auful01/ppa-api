<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvCctv extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'max_id',
        'asset_ho_number',
        'cctv_code',
        'location',
        'location_detail',
        'cctv_name',
        'cctv_brand',
        'type_cctv',
        'mac_address',
        'ip_address',
        'nvr_id',
        'switch_id',
        'date_of_inventory',
        'vlan',
        'uplink',
        'status',
        'note',
        'last_status_ping',
        'last_update_ping',
        'site'
    ];

    public function switch()
    {
        return $this->belongsTo(InvSwitch::class, 'switch_id', 'id')->withDefault([
            'inventory_number' => 'Data switch tidak ditemukan !',
        ]);
    }
}
