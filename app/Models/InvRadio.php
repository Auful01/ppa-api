<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvRadio extends Model
{
    use HasFactory;

    protected $fillable = [
        'radio_code',
        'sarana_number',
        'category_radio',
        'radio_brand',
        'type_radio',
        'model_radio',
        'serial_number',
        'date_of_inventory',
        'status',
        'note',
    ];
}
