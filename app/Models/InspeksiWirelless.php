<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspeksiWirelless extends Model
{
    use HasFactory;

    protected $fillable = [
        'inv_wirelless_id',
        'ip_address',
        'pica_number',
        'created_date',
        'condition',
        'inspection_at',
        'month',
        'year',
        'inspection_status',
        'device_status',
        'findings',
        'findings_image',
        'findings_status',
        'findings_action',
        'action_image',
        'due_date',
        'remarks',
        'inspector',
        'scrap',
        'scrap_note',
        'inventory_status',
        'approved_by',
        'status_approval',

    ];
}
