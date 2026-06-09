<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvMobileTower extends Model
{
    use HasFactory, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'inventory_number',
        'mt_code',
        'type_mt',
        'location',
        'detail_location',
        'gps',
        'led_lamp',
        'condition',
        'status',
        'note',
        'padlock_code',
        'site',
        'inspection_remark'
    ];

        public function inspeksi()
    {
        return $this->hasMany(InspeksiMobileTower::class, 'inv_mt_id', 'id');
    }
}
