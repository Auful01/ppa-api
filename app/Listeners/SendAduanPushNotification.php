<?php

namespace App\Listeners;

use App\Events\AduanAssigned;
use App\Events\AduanCreated;
use App\Events\AduanUpdated;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Turns Aduan domain events into FCM pushes. Recipients mirror the audience that
 * the web toast targeted: every user of the aduan's site (that is exactly who
 * the 2-second web poller showed the SweetAlert to).
 *
 * Implements ShouldQueue so production can offload the HTTP call to a worker;
 * with Laravel's default QUEUE_CONNECTION=sync it simply runs inline.
 */
class SendAduanPushNotification implements ShouldQueue
{
    public function __construct(private PushNotificationService $push)
    {
    }

    public function handle(AduanCreated|AduanAssigned|AduanUpdated $event): void
    {
        $aduan = $event->aduan;

        [$title, $body] = match (true) {
            $event instanceof AduanCreated  => [
                'Aduan Baru!',
                $this->summary($aduan),
            ],
            $event instanceof AduanAssigned => [
                'Aduan Ditugaskan',
                $this->summary($aduan),
            ],
            default => [
                'Update Aduan',
                $this->summary($aduan),
            ],
        };

        $recipients = User::where('site', $aduan->site)->get();
        if ($recipients->isEmpty()) {
            return;
        }

        $this->push->sendToUsers($recipients, $title, $body, [
            // Tapping the notification opens this aduan (mobile reads `aduan_id`).
            'type'           => 'aduan',
            'aduan_id'       => $aduan->id,
            'complaint_code' => (string) ($aduan->complaint_code ?? ''),
            'site'           => (string) ($aduan->site ?? ''),
            'status'         => (string) ($aduan->status ?? ''),
        ]);
    }

    private function summary(\App\Models\Aduan $aduan): string
    {
        $note = trim((string) ($aduan->complaint_note ?? ''));
        $code = trim((string) ($aduan->complaint_code ?? ''));
        $text = $note !== '' ? $note : 'Lihat detail aduan.';
        return $code !== '' ? "[$code] $text" : $text;
    }
}
