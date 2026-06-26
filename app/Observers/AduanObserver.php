<?php

namespace App\Observers;

use App\Events\AduanAssigned;
use App\Events\AduanCreated;
use App\Events\AduanUpdated;
use App\Models\Aduan;

/**
 * Event-driven trigger for Aduan push notifications. Hooking the Eloquent model
 * covers BOTH the web Inertia controllers and the mobile AduanApiController in a
 * single place, WITHOUT touching any Aduan business logic (it only dispatches
 * events after the write has already happened).
 *
 * This is what replaces the web's per-2-second polling for mobile clients.
 */
class AduanObserver
{
    public function created(Aduan $aduan): void
    {
        AduanCreated::dispatch($aduan);
    }

    public function updated(Aduan $aduan): void
    {
        // A change to the crew column is an "assignment"; anything else is a
        // generic update (status / progress / note).
        if ($aduan->wasChanged('crew')) {
            AduanAssigned::dispatch($aduan);
        } else {
            AduanUpdated::dispatch($aduan);
        }
    }
}
