<?php

namespace App\Services\Messaging;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Push notifications over FCM HTTP v1.
 *
 * Configured in Settings → Push Notifications: the Firebase project's
 * service-account JSON (pasted as-is) plus an on/off switch. No Firebase SDK —
 * the service signs its own JWT with the service account's key and talks to
 * the two Google endpoints directly, the same no-dependency approach the SMS
 * and payment gateways use.
 *
 * Never throws: a dead push setup must not take a booking or a payment down
 * with it. Failures are logged to the `sms` channel (messaging log).
 */
class PushService
{
    private const OAUTH_URL = 'https://oauth2.googleapis.com/token';
    private const SCOPE     = 'https://www.googleapis.com/auth/firebase.messaging';

    /** True when the switch is on and the service-account JSON parses. */
    public static function enabled(): bool
    {
        return (bool) settings('fcm_status') && static::credentials() !== null;
    }

    /**
     * Send a push to every device the account is signed in on.
     * Fire-and-forget: returns how many devices accepted it.
     */
    public static function sendTo($account, string $title, ?string $body = null, array $data = []): int
    {
        if (! static::enabled()) {
            return 0;
        }

        $tokens = DeviceToken::tokensFor($account);
        if (! $tokens) {
            return 0;
        }

        $sent = 0;

        foreach ($tokens as $token) {
            if (static::sendToToken($token, $title, $body, $data)) {
                $sent++;
            }
        }

        return $sent;
    }

    /** One message to one registration token. */
    private static function sendToToken(string $token, string $title, ?string $body, array $data): bool
    {
        $creds  = static::credentials();
        $bearer = static::accessToken();

        if (! $creds || ! $bearer) {
            return false;
        }

        try {
            $response = Http::withToken($bearer)
                ->timeout(10)
                ->post("https://fcm.googleapis.com/v1/projects/{$creds['project_id']}/messages:send", [
                    'message' => [
                        'token'        => $token,
                        'notification' => array_filter([
                            'title' => $title,
                            'body'  => $body,
                        ]),
                        // Everything in `data` must be a string for FCM.
                        'data' => array_map('strval', $data),
                    ],
                ]);

            if ($response->successful()) {
                return true;
            }

            // A device that uninstalled the app answers UNREGISTERED — that
            // token is dead, so it leaves the table rather than erroring on
            // every future notification.
            if (in_array($response->status(), [400, 404], true)
                && str_contains($response->body(), 'UNREGISTERED')) {
                DeviceToken::where('token', $token)->delete();
            }

            Log::channel('sms')->warning('[FCM] send failed', [
                'status' => $response->status(),
                'body'   => mb_substr($response->body(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            Log::channel('sms')->warning('[FCM] send error', ['error' => $e->getMessage()]);
        }

        return false;
    }

    /**
     * OAuth2 bearer for the FCM API: a self-signed RS256 JWT exchanged at
     * Google's token endpoint. Cached just under its hour of validity.
     */
    private static function accessToken(): ?string
    {
        $creds = static::credentials();
        if (! $creds) {
            return null;
        }

        return Cache::remember('fcm_access_token', now()->addMinutes(50), function () use ($creds) {
            $now = time();

            $jwt = static::jwt([
                'iss'   => $creds['client_email'],
                'scope' => self::SCOPE,
                'aud'   => self::OAUTH_URL,
                'iat'   => $now,
                'exp'   => $now + 3600,
            ], $creds['private_key']);

            if (! $jwt) {
                return null;
            }

            try {
                $response = Http::asForm()->timeout(10)->post(self::OAUTH_URL, [
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion'  => $jwt,
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::channel('sms')->warning('[FCM] token exchange failed', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 300),
                ]);
            } catch (\Throwable $e) {
                Log::channel('sms')->warning('[FCM] token exchange error', ['error' => $e->getMessage()]);
            }

            return null;
        });
    }

    /** RS256-signed JWT, or null when the private key does not parse. */
    private static function jwt(array $claims, string $privateKey): ?string
    {
        $encode = fn (array $part) => rtrim(strtr(base64_encode(json_encode($part)), '+/', '-_'), '=');

        $payload = $encode(['alg' => 'RS256', 'typ' => 'JWT']) . '.' . $encode($claims);

        $key = openssl_pkey_get_private($privateKey);
        if (! $key || ! openssl_sign($payload, $signature, $key, OPENSSL_ALGO_SHA256)) {
            Log::channel('sms')->warning('[FCM] service-account private key did not parse');

            return null;
        }

        return $payload . '.' . rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
    }

    /**
     * The pasted service-account JSON, validated. Needs project_id,
     * client_email and private_key to be usable.
     */
    private static function credentials(): ?array
    {
        $raw = (string) settings('fcm_service_account');
        if (blank($raw)) {
            return null;
        }

        $creds = json_decode($raw, true);

        if (! is_array($creds)
            || blank($creds['project_id'] ?? null)
            || blank($creds['client_email'] ?? null)
            || blank($creds['private_key'] ?? null)) {
            return null;
        }

        return $creds;
    }
}
