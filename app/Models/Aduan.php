<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Aduan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'complaint_code',
        'complaint_image',
        'inventory_number',
        'response_time',
        'date_of_complaint',
        'created_date',
        'start_response',
        'start_progress',
        'end_progress',
        'category_name',
        'complaint_note',
        'complaint_name',
        'complaint_position',
        'phone_number',
        'nrp',
        'max_id',
        'location',
        'urgency',
        'detail_location',
        'repair_note',
        'repair_image',
        'status',
        'root_cause_id',
        'action_repair',
        'crew',
        'site',
        'site_pelapor',
        'user_rating'
    ];

    public function rootCause()
    {
        return $this->belongsTo(RootCauseProblem::class, 'root_cause_id', 'id_cause');
    }
}
