<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspeksiPanelBoxNetwork extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'inv_panel_box_id',
        'created_date',
        'month',
        'year',
        'inspection_status',
        'findings',
        'findings_image',
        'findings_action',
        'action_image',
        'findings_status',
        'due_date',
        'cleanliness',
        'conditions',
        'remarks',
        'cable_arrangement',
        'inspection_by',
        'inspection_at',
        'approved_by',
        'status_approval',
        'pica_number',
    ];
}
