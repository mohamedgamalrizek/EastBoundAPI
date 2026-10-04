<?php

namespace App\Services\Demo;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

/**
 * The quick-login buttons on the sign-in pages.
 *
 * Two independent gates, both of which must pass:
 *
 *   1. APP_DEMO must be on. This is an env switch, so a buyer who never touches
 *      it can never accidentally publish demo buttons.
 *   2. The account must actually exist in the users table. A buyer who runs the
 *      migrations without the demo seeders, or who deletes the sample accounts,
 *      gets no buttons rather than a row of chips that all fail to log in.
 *
 * The email list lives in config/demo.php, not inline in a view, so it can be
 * changed or emptied without editing Blade.
 */
class DemoLogins
{
    private const CACHE_KEY = 'demo_login_accounts';

    /** Gate 1 — the env switch. */
    public static function enabled(): bool
    {
        return (bool) config('app.demo');
    }

    /**
     * Accounts to offer: configured, and present in the database.
     *
     * @return array<int, array{label: string, email: string, admin: bool}>
     */
    public static function accounts(bool $adminOnly = false, bool $portalOnly = false): array
    {
        if (! static::enabled()) {
            return [];
        }

        $configured = config('saas.enabled')
            ? (array) config('demo.saas', [])
            : (array) config('demo.accounts', []);

        if ($configured === []) {
            return [];
        }

        $existing = static::existingEmails(array_column($configured, 'email'));

        return array_values(array_filter($configured, function (array $account) use ($existing, $adminOnly, $portalOnly) {
            if (! in_array(mb_strtolower($account['email']), $existing, true)) {
                return false;
            }

            if ($adminOnly) {
                return (bool) ($account['admin'] ?? false);
            }

            if ($portalOnly) {
                return ! ($account['admin'] ?? false);
            }

            return true;
        }));
    }

    /** True when there is at least one button worth rendering. */
    public static function available(bool $adminOnly = false, bool $portalOnly = false): bool
    {
        return static::accounts($adminOnly, $portalOnly) !== [];
    }

    /**
     * Which of the configured emails exist, lowercased.
     *
     * Cached briefly: this runs on every render of the login page, and the
     * answer only changes when accounts are created or deleted. A missing or
     * unmigrated users table must not take the login page down with it.
     *
     * @param  array<int, string>  $emails
     * @return array<int, string>
     */
    private static function existingEmails(array $emails): array
    {
        try {
            return Cache::remember(
                tenant_cache_prefix() . self::CACHE_KEY,
                now()->addMinutes(10),
                fn () => User::whereIn('email', $emails)
                    ->pluck('email')
                    ->map(fn ($email) => mb_strtolower((string) $email))
                    ->all()
            );
        } catch (\Throwable) {
            return [];
        }
    }

    /** Drop the cache — call after seeding or deleting demo accounts. */
    public static function forget(): void
    {
        Cache::forget(tenant_cache_prefix() . self::CACHE_KEY);
    }
}
