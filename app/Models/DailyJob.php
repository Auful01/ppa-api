<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class DailyJob extends Model
{
    protected $fillable = [
        'code',
        'category_job',
        'description',
        'site',
        'category',
        'shift',
        'date',
        'due_date',
        'start_progress',
        'end_progress',
        'issue',
        'root_cause',
        'action_taken',
        'remark',
        'status',
        'urgency',
        'crew',
        'sarana',
        'created_by',
        'updated_by',
        'is_approved',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'crew' => 'array',
        'date' => 'date',
        'start_progress' => 'datetime',
        'end_progress' => 'datetime',
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function crewMembers()
    {
        return User::whereIn('id', $this->crew)->get();
    }
}
