<?php

namespace App\Events;

use App\Models\Aduan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired by AduanObserver when an Aduan's crew (assignment) changes. */
class AduanAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public Aduan $aduan)
    {
    }
}
