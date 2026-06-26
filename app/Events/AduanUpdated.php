<?php

namespace App\Events;

use App\Models\Aduan;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/** Fired by AduanObserver on a non-assignment Aduan update (e.g. status/progress). */
class AduanUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(public Aduan $aduan)
    {
    }
}
