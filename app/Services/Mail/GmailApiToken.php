<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * OAuth2 access tokens for the Gmail API, minted from a long-lived refresh
 * token held in Settings -> Mail.
 *
 * Why this exists: shared hosts very often block outbound 587/465, which kills
 * SMTP dead with a timeout the buyer cannot fix. The Gmail API is plain HTTPS
 * on 443, so it works anywhere the server can reach the internet at all.
 *
 * No Google SDK — the same no-dependency approach PushService uses for FCM.
 */
class GmailApiToken
{
    public const OAUTH_URL = 'https://oauth2.googleapis.com/token';

    /** The only scope needed: send as the authorised account, nothing else. */
    public const SCOPE = 'https://www.googleapis.com/auth/gmail.send';

    private const CACHE_KEY = 'gmail_api_access_token';

    /** True when all three OAuth values are present. */
    public static function configured(): bool
    {
        return static::clientId() !== ''
            && static::clientSecret() !== ''
            && static::refreshToken() !== '';
    }

    public static function clientId(): string
    {
        return trim((string) settings('gmail_client_id'));
    }

    public static function clientSecret(): string
    {
        return trim((string) decrypt_setting('gmail_client_secret'));
    }

    public static function refreshToken(): string
    {
        return trim((string) decrypt_setting('gmail_refresh_token'));
    }

    /**
     * A bearer valid for this send.
     *
     * Google returns an hour of validity; it is cached just under that so a
     * busy queue does not re-mint one per message, and forgotten on failure so
     * a rotated secret is picked up on the next attempt rather than after an
     * hour of bounces.
     */
    public static function accessToken(): ?string
    {
        if (! static::configured()) {
            return null;
        }

        return Cache::remember(static::CACHE_KEY, now()->addMinutes(50), function () {
            try {
                $response = Http::asForm()->timeout(15)->post(static::OAUTH_URL, [
                    'client_id'     => static::clientId(),
                    'client_secret' => static::clientSecret(),
                    'refresh_token' => static::refreshToken(),
                    'grant_type'    => 'refresh_token',
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                // invalid_grant = the refresh token was revoked, expired
                // (unverified apps expire in 7 days) or belongs to another
                // client. Say so plainly; the generic message sends people
                // hunting through firewall rules for a consent problem.
                Log::channel('sms')->warning('[Gmail API] token refresh failed', [
                    'status' => $response->status(),
                    'body'   => mb_substr($response->body(), 0, 500),
                ]);
            } catch (\Throwable $e) {
                Log::channel('sms')->warning('[Gmail API] token refresh error', [
                    'error' => $e->getMessage(),
                ]);
            }

            return null;
        });
    }

    /** Drop the cached bearer — called after a 401 and whenever settings change. */
    public static function forget(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
