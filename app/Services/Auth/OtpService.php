<?php

namespace App\Services\Auth;

use App\Mail\OtpCode;
use App\Services\Mail\MailConfig;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * One place that issues and checks the numeric codes used for account
 * verification and password reset — web and app, staff and customers.
 *
 * There used to be two mechanisms: the web mailed a 5-digit `users.token` that
 * never expired, while the app texted a 6-digit `customers.otp` that expired in
 * five minutes. Two implementations meant the hardening only ever landed on one
 * of them. This is the single implementation both now use.
 *
 * Delivery is email. The SMS gateway remains in the product for notifications,
 * but nobody needs to buy one before a customer can register or recover an
 * account — SMTP is enough.
 */
class OtpService
{
    /** Codes live long enough to arrive and be typed, not long enough to hoard. */
    public const TTL_MINUTES = 10;

    /**
     * Issue a fresh code, store it with an expiry, and mail it.
     *
     * The code is never returned to the caller and never appears in a response:
     * it exists only in the database and in the email. Returns false when the
     * account has no address or the mail could not be handed to the transport,
     * so the caller can say so instead of leaving the user waiting.
     */
    public static function issue(Model $account): bool
    {
        $email = trim((string) ($account->email ?? ''));

        if ($email === '') {
            return false;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $account->forceFill(static::columns($account, $code, now()->addMinutes(self::TTL_MINUTES)))->save();

        try {
            MailConfig::apply();
            Mail::to($email)->send(new OtpCode($account->name ?? '', $code, self::TTL_MINUTES));

            return true;
        } catch (\Throwable $e) {
            // A dead mail server must not take the request down with it — the
            // account is already created, and the user can ask for a resend.
            Log::warning('[OTP] could not send code', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * True when the code matches and has not expired.
     *
     * hash_equals so a wrong code cannot be discovered a digit at a time by
     * timing the response. A blank stored code never matches.
     */
    public static function check(Model $account, string $code): bool
    {
        [$codeColumn, $expiryColumn] = static::columnNames($account);

        $stored = (string) ($account->{$codeColumn} ?? '');
        $expiry = $account->{$expiryColumn};

        if ($stored === '' || $code === '') {
            return false;
        }

        if ($expiry !== null && now()->greaterThan($expiry)) {
            return false;
        }

        return hash_equals($stored, $code);
    }

    /** Burn the code so it cannot be replayed. */
    public static function clear(Model $account): void
    {
        [$codeColumn, $expiryColumn] = static::columnNames($account);

        $account->forceFill([$codeColumn => null, $expiryColumn => null])->save();
    }

    /**
     * Customers keep theirs in `otp`, users in `token` — the two tables were
     * built at different times. Mapped here so callers never have to care.
     *
     * @return array{0: string, 1: string}
     */
    private static function columnNames(Model $account): array
    {
        return $account->getTable() === 'customers'
            ? ['otp', 'otp_expires_at']
            : ['token', 'token_expires_at'];
    }

    /** @return array<string, mixed> */
    private static function columns(Model $account, string $code, $expiresAt): array
    {
        [$codeColumn, $expiryColumn] = static::columnNames($account);

        return [$codeColumn => $code, $expiryColumn => $expiresAt];
    }
}
