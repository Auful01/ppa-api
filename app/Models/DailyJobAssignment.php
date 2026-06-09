<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyJobAssignment extends Model
{
    use HasFactory;
    protected $fillable = [
        'assignment_code',
        'job_assignment',
        'remark',
        'job',
        'sarana',
        'due_date',
        'status',
        'action',
        'shift',
        'crew_partner',
        'job_category',
    ];
}
