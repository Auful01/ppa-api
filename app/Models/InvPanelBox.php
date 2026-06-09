<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvPanelBox extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'inventory_number',
        'panel_type',
        'location',
        'note',
        'status',
    ];
}
