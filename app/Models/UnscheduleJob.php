<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnscheduleJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'activity_code',
        'category_report',
        'category',
        'start_progress',
        'end_progress',
        'issue',
        'root_cause',
        'action',
        'crew',
        'status',
        'urgency',
        'remark',
    ];
}
