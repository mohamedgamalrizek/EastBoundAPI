<?php

namespace App\Services\Contact;

/**
 * Phone numbers, stored one way: E.164 (+8801711000111).
 *
 * `customers.phone` is both a unique key and a login identifier, but it used to
 * hold whatever the user typed. Because uniqueness and lookup are plain string
 * comparisons, that meant one person could hold three accounts —
 * `01711000111`, `+8801711000111`, `8801711000111` — and someone who signed up
 * in one format could not log in typing another. Normalising on the way in and
 * on every lookup is what actually fixes that; the country picker is only the
 * UI that makes it enterable.
 *
 * Deliberately not a full libphonenumber: that is a large dependency for a
 * marketplace item, and the rules below cover the shapes people actually type.
 * Numbers that cannot be understood are returned digit-cleaned rather than
 * mangled, so nothing is ever silently lost.
 */
class PhoneNumber
{
    /** Dial code used when the number carries no country of its own. */
    public static function defaultDialCode(): string
    {
        $code = preg_replace('/\D/', '', (string) settings('default_dial_code'));

        return $code !== '' ? $code : '880';
    }

    /**
     * Normalise to E.164.
     *
     *   +880 1711-000111  -> +8801711000111
     *   008801711000111   -> +8801711000111
     *   01711000111       -> +8801711000111   (leading 0 is the trunk prefix)
     *   8801711000111     -> +8801711000111
     *   1711000111        -> +8801711000111
     *
     * An empty input stays empty — the column is nullable and a blank phone is
     * a legitimate state, not something to invent a country for.
     */
    public static function e164(?string $input, ?string $dialCode = null): string
    {
        $raw = trim((string) $input);

        if ($raw === '') {
            return '';
        }

        $dial = preg_replace('/\D/', '', (string) ($dialCode ?? static::defaultDialCode()));
        $hasPlus = str_starts_with($raw, '+');
        $digits = preg_replace('/\D/', '', $raw);

        if ($digits === '') {
            return '';
        }

        // Already international, either written with + or dialled with 00.
        if ($hasPlus) {
            return '+' . $digits;
        }

        if (str_starts_with($digits, '00')) {
            return '+' . substr($digits, 2);
        }

        // National format: the leading 0 is a trunk prefix, not part of the
        // number, so it is dropped before the country code goes on.
        if (str_starts_with($digits, '0')) {
            return '+' . $dial . ltrim(substr($digits, 1), '0');
        }

        // Already carries the country code without a plus.
        if ($dial !== '' && str_starts_with($digits, $dial)) {
            return '+' . $digits;
        }

        return '+' . $dial . $digits;
    }

    /**
     * The dial code an E.164 number belongs to, longest match first.
     *
     * Dial codes are not fixed width (+1, +44, +880), and some are prefixes of
     * others, so the longest match is the correct one.
     */
    public static function dialCodeOf(?string $e164): string
    {
        $digits = preg_replace('/\D/', '', (string) $e164);

        if ($digits === '' || ! str_starts_with(trim((string) $e164), '+')) {
            return '';
        }

        $codes = array_column(config('dial_codes', []), 'dial');
        usort($codes, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($codes as $dial) {
            if ($dial !== '' && str_starts_with($digits, $dial)) {
                return $dial;
            }
        }

        return '';
    }

    /**
     * The subscriber part, with the country code taken off — what a phone
     * input should show next to its country picker.
     */
    public static function nationalPart(?string $input, ?string $dialCode = null): string
    {
        $raw = trim((string) $input);

        if ($raw === '') {
            return '';
        }

        $e164 = static::e164($raw, $dialCode);
        $dial = $dialCode ?: static::dialCodeOf($e164);
        $digits = ltrim($e164, '+');

        return ($dial !== '' && str_starts_with($digits, (string) $dial))
            ? substr($digits, strlen((string) $dial))
            : $digits;
    }

    /**
     * Every spelling of a number that might be sitting in the database.
     *
     * Existing rows were written before normalisation, so a lookup has to match
     * the stored legacy shapes too — otherwise upgrading the package would lock
     * every existing customer out of their own account.
     *
     * @return array<int, string>
     */
    public static function lookupVariants(?string $input, ?string $dialCode = null): array
    {
        $raw = trim((string) $input);

        if ($raw === '') {
            return [];
        }

        $e164 = static::e164($raw, $dialCode);
        $dial = preg_replace('/\D/', '', (string) ($dialCode ?? static::defaultDialCode()));
        $national = $e164 !== '' ? substr($e164, 1) : '';           // 8801711000111

        $variants = [$raw, $e164, $national];

        if ($dial !== '' && str_starts_with($national, $dial)) {
            $local = substr($national, strlen($dial));              // 1711000111
            $variants[] = $local;
            $variants[] = '0' . $local;                             // 01711000111
        }

        return array_values(array_unique(array_filter($variants)));
    }
}
