<?php

namespace App\Console\Commands;

use App\Models\Aduan;
use App\Support\AduanPushNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Realtime push for aduans created by the SEPARATE web (Inertia) project.
 *
 * The two Laravel apps share one database but not the runtime, so this project's
 * Eloquent observer never fires for a web-created row. This long-running daemon
 * (managed by Supervisor) polls the shared `aduans` table every ~2s for rows it
 * has not pushed yet (`push_sent_at IS NULL`, indexed — never a full-table
 * scan), claims each one atomically and sends the existing FCM push.
 *
 * Restart-safe: only unprocessed rows are picked up; a marked row is never
 * resent, and rows created while the daemon was down still have NULL so they are
 * delivered on the next start (no lost events).
 */
class WatchAduan extends Command
{
    protected $signature = 'aduan:watch
        {--sleep=2 : Seconds between polls (clamped to 1–3)}
        {--limit=200 : Max new aduans handled per poll}
        {--once : Run a single poll and exit (for testing / scheduler use)}';

    protected $description = 'Watch the shared aduans table and push FCM notifications for newly created complaints (web + mobile).';

    private bool $shouldStop = false;

    public function handle(AduanPushNotifier $notifier): int
    {
        $sleep = min(max((int) $this->option('sleep'), 1), 3);
        $limit = max((int) $this->option('limit'), 1);

        $this->installSignalHandlers();
        $this->info("[aduan:watch] started (sleep={$sleep}s, limit={$limit})");

        do {
            try {
                $count = $this->tick($notifier, $limit);
                if ($count > 0) {
                    $this->line('[' . now()->toDateTimeString() . "] pushed {$count} new aduan(s)");
                }
            } catch (\Throwable $e) {
                // Never let one bad poll kill the daemon — log and keep looping.
                Log::error('[aduan:watch] poll failed', ['error' => $e->getMessage()]);
            }

            if ($this->option('once') || $this->shouldStop) {
                break;
            }

            // Sleep in 1s slices so a SIGTERM (supervisor stop / deploy) is
            // honoured within ~1s instead of up to `sleep` seconds.
            for ($i = 0; $i < $sleep && ! $this->shouldStop; $i++) {
                sleep(1);
            }
        } while (! $this->shouldStop);

        $this->info('[aduan:watch] stopped gracefully');

        return self::SUCCESS;
    }

    /** One poll: claim + push every not-yet-notified aduan. Returns #sent. */
    private function tick(AduanPushNotifier $notifier, int $limit): int
    {
        // SoftDeletes global scope already excludes trashed rows. Oldest first
        // so a backlog drains in creation order.
        $candidates = Aduan::query()
            ->whereNull('push_sent_at')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        $sent = 0;
        foreach ($candidates as $aduan) {
            try {
                if ($notifier->created($aduan)) {
                    $sent++;
                }
            } catch (\Throwable $e) {
                Log::error('[aduan:watch] push failed for one aduan', [
                    'id'    => $aduan->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    private function installSignalHandlers(): void
    {
        if (! function_exists('pcntl_async_signals')) {
            return; // e.g. Windows dev — daemon still works, just no graceful trap
        }
        pcntl_async_signals(true);
        $stop = function (): void {
            $this->shouldStop = true;
        };
        pcntl_signal(SIGTERM, $stop);
        pcntl_signal(SIGINT, $stop);
    }
}
