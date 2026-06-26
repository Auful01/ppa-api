<?php

namespace App\Services;

use App\Models\DeviceToken;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends Firebase Cloud Messaging (FCM) push notifications using the modern
 * HTTP v1 API. Authentication uses a Google service-account JSON; the OAuth2
 * access token is minted with a pure-PHP RS256 JWT (openssl) so NO extra
 * composer package is required, and it is cached for ~55 minutes.
 *
 * Configuration (config/services.php → 'fcm'):
 *   FCM_CREDENTIALS = absolute path to the service-account .json
 *   FCM_PROJECT_ID  = Firebase project id (optional; read from the json if omitted)
 *
 * The service is FAIL-SAFE: when FCM is not configured (e.g. local dev) every
 * call logs a notice and returns without throwing, so the Aduan business flow is
 * never affected. See docs/ADUAN_PUSH_NOTIFICATION.md.
 */
class PushNotificationService
{
    /**
     * Push to every device token of a single user.
     */
    /**
     * @return array<int,array{token:string,ok:bool,status:?int,response:string}>
     *         Per-token send results (also used by the push:test artisan command).
     */
    public function sendToUser(User $user, string $title, string $body, array $data = []): array
    {
        return $this->sendToTokens($user->deviceTokens()->pluck('token'), $title, $body, $data);
    }

    /**
     * Push to every device token of a set of users (e.g. all crew of a site).
     *
     * @param  Collection<int,User>|array<int,User>  $users
     * @return array<int,array{token:string,ok:bool,status:?int,response:string}>
     */
    public function sendToUsers($users, string $title, string $body, array $data = []): array
    {
        $userIds = collect($users)->pluck('id')->filter()->unique()->values();
        if ($userIds->isEmpty()) {
            return [];
        }
        $tokens = DeviceToken::whereIn('user_id', $userIds)->pluck('token');
        return $this->sendToTokens($tokens, $title, $body, $data);
    }

    /**
     * Push to an explicit list of tokens. This is the SINGLE send implementation
     * used by both the AduanObserver listener and the push:test command.
     *
     * @param  Collection<int,string>|array<int,string>  $tokens
     * @return array<int,array{token:string,ok:bool,status:?int,response:string}>
     */
    public function sendToTokens($tokens, string $title, string $body, array $data = []): array
    {
        $tokens = collect($tokens)->filter()->unique()->values();
        if ($tokens->isEmpty()) {
            return [];
        }

        $accessToken = $this->accessToken();
        $projectId = $this->projectId();
        if (! $accessToken || ! $projectId) {
            Log::notice('[FCM] skipped — credentials not configured.', [
                'recipients' => $tokens->count(),
                'title'      => $title,
            ]);
            return $tokens
                ->map(fn ($token) => [
                    'token'    => (string) $token,
                    'ok'       => false,
                    'status'   => null,
                    'response' => 'FCM not configured (services.fcm.credentials missing).',
                ])
                ->all();
        }

        // FCM v1 sends one message per token. Data values MUST be strings.
        $stringData = array_map(fn ($v) => (string) $v, $data);

        $results = [];
        foreach ($tokens as $token) {
            try {
                $response = Http::withToken($accessToken)
                    ->timeout(10)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token'        => $token,
                            'notification' => ['title' => $title, 'body' => $body],
                            'data'         => $stringData,
                            'android'      => ['priority' => 'high'],
                            'apns'         => [
                                'payload' => ['aps' => ['sound' => 'default']],
                            ],
                        ],
                    ]);

                // 404 / UNREGISTERED → token is dead, prune it.
                if ($response->status() === 404 || $response->status() === 400) {
                    DeviceToken::where('token', $token)->delete();
                }

                $results[] = [
                    'token'    => (string) $token,
                    'ok'       => $response->successful(),
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ];
            } catch (\Throwable $e) {
                Log::warning('[FCM] send failed', ['error' => $e->getMessage()]);
                $results[] = [
                    'token'    => (string) $token,
                    'ok'       => false,
                    'status'   => null,
                    'response' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Mint (and cache) an OAuth2 access token from the service-account JSON.
     */
    private function accessToken(): ?string
    {
        $credentials = $this->credentials();
        if (! $credentials) {
            return null;
        }

        return Cache::remember('fcm_access_token', now()->addMinutes(55), function () use ($credentials) {
            $now = time();
            $claim = [
                'iss'   => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $jwt = $this->signJwt($claim, $credentials['private_key']);
            if (! $jwt) {
                return null;
            }

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            return $response->ok() ? $response->json('access_token') : null;
        });
    }

    /**
     * Build & RS256-sign a JWT with openssl (no external library).
     */
    private function signJwt(array $claim, string $privateKey): ?string
    {
        $encode = fn ($data) => rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
        $header = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $encode(json_encode($claim));
        $signingInput = "$header.$payload";

        $signature = '';
        if (! openssl_sign($signingInput, $signature, $privateKey, 'sha256WithRSAEncryption')) {
            Log::warning('[FCM] JWT signing failed.');
            return null;
        }

        return "$signingInput.{$encode($signature)}";
    }

    /**
     * @return array{client_email:string,private_key:string,project_id?:string}|null
     */
    private function credentials(): ?array
    {
        $path = config('services.fcm.credentials');
        if (! $path || ! is_file($path)) {
            return null;
        }
        $json = json_decode((string) file_get_contents($path), true);
        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            return null;
        }
        return $json;
    }

    private function projectId(): ?string
    {
        $configured = config('services.fcm.project_id');
        if ($configured) {
            return $configured;
        }
        $credentials = $this->credentials();
        return $credentials['project_id'] ?? null;
    }
}
