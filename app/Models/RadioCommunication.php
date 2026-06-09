<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadioCommunication extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_report',
        'start_response',
        'start_time',
        'finish_time',
        'report_date',
        'problem',
        'action',
        'nrp_operator',
        'unit_code',
        'findings',
        'issue_details',
        'swr_results',
        'job_category',
        'shift',
        'repair_note',
        'status',
    ];
}
