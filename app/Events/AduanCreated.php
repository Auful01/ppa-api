<?php

namespace App\Events;

use App\Models\Aduan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired by AduanObserver when a new Aduan row is created (web or mobile). */
class AduanCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Aduan $aduan)
    {
    }
}
