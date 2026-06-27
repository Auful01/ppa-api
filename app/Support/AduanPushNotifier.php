<?php

namespace App\Support;

use App\Models\Aduan;
use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\DB;

/**
 * Single place that turns "a new Aduan exists" into exactly ONE FCM push,
 * regardless of who noticed it first:
 *   - the queued SendAduanPushNotification listener (mobile-created rows), or
 *   - the `aduan:watch` daemon (web-created rows, and a safety net for mobile).
 *
 * Deduplication is an atomic claim on `push_sent_at` (NULL -> now()). Whoever
 * flips it wins and sends; everyone else is a no-op. This also makes it safe to
 * run more than one watcher worker.
 */
class AduanPushNotifier
{
    public function __construct(private PushNotificationService $push)
    {
    }

    /**
     * Atomically claim the create-push for $aduan and send it.
     *
     * @return bool true if THIS caller sent the push, false if it was already
     *              claimed/sent elsewhere (so the caller should do nothing).
     */
    public function created(Aduan $aduan): bool
    {
        // Atomic claim — a single UPDATE that only the first caller can win.
        $claimed = DB::table('aduans')
            ->where('id', $aduan->id)
            ->whereNull('push_sent_at')
            ->update(['push_sent_at' => now()]);

        if ($claimed === 0) {
            return false; // already notified by another worker / the listener
        }

        try {
            $recipients = User::where('site', $aduan->site)->get();
            if ($recipients->isNotEmpty()) {
                $this->push->sendToUsers(
                    $recipients,
                    'Aduan Baru!',
                    self::summary($aduan),
                    [
                        // Tapping the notification opens this aduan (mobile reads `aduan_id`).
                        'type'           => 'aduan',
                        'aduan_id'       => (string) $aduan->id,
                        'complaint_code' => (string) ($aduan->complaint_code ?? ''),
                        'site'           => (string) ($aduan->site ?? ''),
                        'status'         => (string) ($aduan->status ?? ''),
                    ]
                );
            }

            return true;
        } catch (\Throwable $e) {
            // Release the claim so the next tick / queue retry can resend it
            // (FCM send is otherwise fail-safe, so this path is rare).
            DB::table('aduans')->where('id', $aduan->id)->update(['push_sent_at' => null]);
            throw $e;
        }
    }

    public static function summary(Aduan $aduan): string
    {
        $note = trim((string) ($aduan->complaint_note ?? ''));
        $code = trim((string) ($aduan->complaint_code ?? ''));
        $text = $note !== '' ? $note : 'Lihat detail aduan.';

        return $code !== '' ? "[$code] $text" : $text;
    }
}
