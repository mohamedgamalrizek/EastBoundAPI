<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Outer filter for the mobile API: a shared `X-App-Key` header our own clients
 * carry, so scripts hitting the endpoints with no headers are turned away.
 *
 * Be clear about what this is worth. A key shipped inside an APK can be pulled
 * back out of it, so this is NOT authentication and it is not a substitute for
 * one. What it buys is the removal of low-effort traffic — someone pointing a
 * scraper at /api/v1/tours to lift an agency's packages and prices — plus a
 * kill-switch: rotate the key in the admin panel and every stale client stops.
 * Real protection is the token auth on private routes and the rate limiting on
 * the public ones.
 *
 * Behaviour, in order:
 *   - No key configured  -> feature off, nothing is enforced. A fresh install
 *     seeds DEFAULT_KEY (the same value the shipped app carries), so the pair
 *     works out of the box; clearing the setting switches the check off.
 *   - Browser request from one of our own web origins -> exempt. A browser
 *     cannot hold a secret header, and those requests are already covered by
 *     CORS and the session.
 *   - Everything else must present the key.
 */
class EnsureAppKey
{
    /** Settings key holding the admin-managed value (Settings -> API Security). */
    public const SETTING_KEY = 'app_api_key';

    /** Settings key recording when that value was last generated. */
    public const SETTING_GENERATED_AT = 'app_api_key_generated_at';

    /**
     * The key every fresh install starts with. Seeded by SettingSeeder and
     * compiled into the Flutter app's env/*.json, so the app talks to a new
     * backend with no key setup at all. Rotate it from Settings -> API
     * Security once the app is rebuilt with the new value; the panel keeps
     * working either way.
     */
    public const DEFAULT_KEY = 'flow-app-key-7c2e9a4d1f8b6035';

    /**
     * The key currently in force.
     *
     * The admin-managed value wins, so it can be rotated from the panel on
     * hosts where `.env` is not writable or is replaced on every deploy.
     * `APP_API_KEY` in `.env` remains a bootstrap fallback.
     */
    public static function configuredKey(): string
    {
        try {
            $stored = (string) settings(self::SETTING_KEY);
        } catch (\Throwable) {
            // Settings table not migrated yet (fresh install, mid-installer) —
            // never let that 500 every API request.
            $stored = '';
        }

        return $stored !== '' ? $stored : (string) config('app.api_key');
    }

    public function handle(Request $request, Closure $next): Response
    {
        $configured = self::configuredKey();

        if ($configured === '') {
            return $next($request);
        }

        if ($this->isOwnWebFrontend($request)) {
            return $next($request);
        }

        $provided = (string) $request->header('X-App-Key');

        // hash_equals, not ===, so a wrong key cannot be discovered a byte at
        // a time by timing the response.
        if ($provided === '' || ! hash_equals($configured, $provided)) {
            abort(401, 'Invalid or missing application key.');
        }

        return $next($request);
    }

    /**
     * True when the request's browser Origin/Referer is one of our own web
     * frontends. Mobile apps and server-to-server callers send no browser
     * Origin, so they fall through and must present the key.
     */
    private function isOwnWebFrontend(Request $request): bool
    {
        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if (! $origin) {
            return false;
        }

        $host = parse_url($origin, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        // Same-origin: covers the Blade frontend in dev and in production with
        // nothing hardcoded, since the two always share a host.
        if ($host === $request->getHost()) {
            return true;
        }

        // Plus any frontend served from a different origin — the Next.js site
        // on its own domain, or a dev port. Same list CORS uses.
        $extraHosts = array_values(array_filter(array_map(
            static fn ($url) => $url ? parse_url($url, PHP_URL_HOST) : null,
            array_merge(
                [env('FRONTEND_URL'), env('APP_URL')],
                preg_split('/\s*,\s*/', (string) env('CORS_ALLOWED_ORIGINS'), -1, PREG_SPLIT_NO_EMPTY) ?: []
            )
        )));

        return in_array($host, $extraHosts, true);
    }
}
