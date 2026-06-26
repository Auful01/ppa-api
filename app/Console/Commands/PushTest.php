<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PushNotificationService;
use Illuminate\Console\Command;

/**
 * Development-only helper: send a test FCM push to a user (identified by NRP,
 * the same field used at login). Reuses the EXACT same path as AduanObserver —
 * PushNotificationService — so no notification logic is duplicated.
 *
 *   php artisan push:test 23002073
 */
class PushTest extends Command
{
    protected $signature = 'push:test {nrp : The user NRP (same field used at login)}';

    protected $description = 'Send a development test push notification to a user by NRP';

    public function handle(PushNotificationService $push): int
    {
        $nrp = (string) $this->argument('nrp');

        // 1) Find the user by the SAME field used during login.
        $user = User::where('nrp', $nrp)->first();
        if (! $user) {
            $this->error("User with NRP {$nrp} not found.");
            return self::FAILURE;
        }

        $tokens = $user->deviceTokens()->pluck('token');

        // 2) Print user details.
        $this->info('User found:');
        $this->line('  Name           : ' . ($user->name ?? '-'));
        $this->line('  NRP            : ' . ($user->nrp ?? '-'));
        $this->line('  User ID        : ' . $user->id);
        $this->line('  Device tokens  : ' . $tokens->count());

        if ($tokens->isEmpty()) {
            $this->warn('No registered device tokens for this user.');
            return self::FAILURE;
        }

        // 3-6) Send via the existing service (same path as AduanObserver).
        $this->newLine();
        $this->info('Sending test push...');
        $results = $push->sendToUser(
            $user,
            'PPA Push Test',
            'This is a development push notification.',
            [
                'type'     => 'aduan',
                'aduan_id' => 'test-uuid',
                'source'   => 'artisan-test',
            ]
        );

        // 7) Print FCM response + success/failure per device token.
        $this->newLine();
        $success = 0;
        foreach ($results as $i => $result) {
            $token = (string) $result['token'];
            $masked = strlen($token) > 16
                ? substr($token, 0, 10) . '…' . substr($token, -6)
                : $token;
            $status = $result['status'] ?? 'n/a';

            if ($result['ok']) {
                $success++;
                $this->info(sprintf('[%d] SUCCESS (HTTP %s) %s', $i + 1, $status, $masked));
            } else {
                $this->error(sprintf('[%d] FAILED  (HTTP %s) %s', $i + 1, $status, $masked));
            }
            $this->line('     FCM response: ' . trim((string) $result['response']));
        }

        $this->newLine();
        $this->line(sprintf('Done: %d/%d device token(s) succeeded.', $success, count($results)));

        return $success > 0 ? self::SUCCESS : self::FAILURE;
    }
}
