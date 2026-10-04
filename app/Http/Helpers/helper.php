<?php

use App\Models\Upload;
use App\Models\Backend\Setting;
use App\Models\Backend\Language;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Schema;



// Tenant-aware cache key prefix. In tenant context returns "tenant_{id}_" so
// each tenant's cached settings/favicon stay isolated from the central app and
// from one another; in central context returns "" (keeps legacy keys intact).
if (!function_exists('tenant_cache_prefix')) {
    function tenant_cache_prefix()
    {
        if (function_exists('tenancy') && optional(tenancy())->initialized) {
            return 'tenant_' . tenant('id') . '_';
        }
        return '';
    }
}

// Global Settings
if (!function_exists('settings')) {
    function settings($key = "")
    {
        try {
            if (!Schema::hasTable('settings')) {
                return null;
            }
        } catch (Throwable) {
            return null;
        }

        $settings = Cache::rememberForever(tenant_cache_prefix() . "settings", fn () => Setting::pluck('value', 'key')->toArray());

        return data_get($settings, $key);
    }
}

if (!function_exists('getImage')) {
    function getImage($upload, ?string $version = null, string $default_image = 'default-image.png')    {
        if ($upload && $upload->{$version} && File::exists(public_path($upload->{$version}))) {

            return asset($upload->{$version});
        }

        if ($default_image && File::exists(public_path("images/default/$default_image"))) {
            return asset("images/default/$default_image");
        }

        return "https://placehold.co/200x200?text=No+Image";
    }
}


//logo
if (!function_exists('logo')) {
    function logo($upload_id = null)
    {
        $logo   = Upload::find($upload_id);
        if ($logo && File::exists(public_path($logo->original))) :
            return asset($logo->original);
        endif;
        // Fallback to a guaranteed-present asset (the old favicon.png path did
        // not exist, producing a broken image when no logo was set).
        return getImage(null, 'original', 'default-image.png');
    }
}

// favicon
if (!function_exists('favicon')) {
    function favicon($upload_id = null)
    {
        $id = $upload_id ?? settings('favicon');

        $cacheKey = tenant_cache_prefix() . "favicon";

        $favicon = Cache::get($cacheKey);

        if ($favicon == null || !File::exists(public_path($favicon->original))) {
            Cache::forget($cacheKey);
            $favicon = Cache::rememberForever($cacheKey, fn () => Upload::find($id));
        }

        return getImage($favicon, 'original');
    }
}

//hasPermission
if (!function_exists('hasPermission')) {
    function hasPermission($permission = null)
    {
        if (in_array($permission, auth()->user()->permissions)) {
            return true;
        }
        return false;
    }
}

// date format
// The format settings are optional; without a fallback `date(null, …)` returns
// an empty string, which silently blanked every formatted date on the site.
if (!function_exists('dateFormat')) {
    function dateFormat($newDate = null)
    {
        return date(settings('date_format') ?: 'd M Y', strtotime($newDate));
    }
}

if (!function_exists('timeFormat')) {
    function timeFormat($newDate = null)
    {
        return date(settings('time_format') ?: 'h:i A', strtotime($newDate));
    }
}

if (!function_exists('dateTimeFormat')) {
    function dateTimeFormat($datetime = null)
    {
        return date(
            (settings('date_format') ?: 'd M Y') . ' ' . (settings('time_format') ?: 'h:i A'),
            strtotime($datetime)
        );
    }
}
//end date format

function ___($key = null, $replace = [], $locale = null)
{
    try {

        $input       = explode('.', $key);
        $file        = $input[0];
        $term        = $input[1];

        $app_local   = session('locale', settings('language'));

        if ($app_local == "") {
            $app_local = 'en';
        }

        $jsonString  = file_get_contents(base_path('lang/' . $app_local . '/' . $file . '.json'));

        $data        = json_decode($jsonString, true);

        // if (config('app.env') == 'local') {
        //     $data[$term] =  ucwords(str_replace(['_', '-'], ' ', $term));
        //     $updatedJsonString = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        //     file_put_contents(base_path('lang/' . $app_local . '/' . $file . '.json'), $updatedJsonString);
        // }

        return $data[$term] ?? $term;
    } catch (\Exception $e) {
        return $key;
    }
}


if (!function_exists('defaultLanguage')) {
    function defaultLanguage()
    {
        $app_local  = Session::get('locale') ?? settings('language');

        if ($app_local == '') {
            $app_local = 'en';
        }

        $cacheKey = "defaultLanguage-{$app_local}";

        if (Cache::has($cacheKey)) {

            $language = Cache::get($cacheKey);

            Cache::put($cacheKey, $language,  Carbon\Carbon::now()->addMinutes(5));  // Extend the cache expiration time by 5 minutes

            return  $language;
        }

        return Cache::remember(
            $cacheKey,
            Carbon\Carbon::now()->addMinutes(10),
            fn () => Language::where('code', $app_local)->first() ?? Language::first() ?? defaultLanguageFallback()
        );
    }
}

if (!function_exists('defaultLanguageFallback')) {
    /**
     * Stand-in used when no language row matches — layouts do
     * `defaultLanguage()->text_direction`, so returning null there white-screens
     * the whole auth area (and anything else using the shared layouts).
     */
    function defaultLanguageFallback(): Language
    {
        // forceFill, not the constructor: Language guards mass assignment.
        return (new Language)->forceFill([
            'name'           => 'English',
            'code'           => 'en',
            'text_direction' => 'LTR',
        ]);
    }
}


/*
|--------------------------------------------------------------------------
| Public website helpers
|--------------------------------------------------------------------------
| Used by the frontend blades so CMS-managed rows can store either an absolute
| URL (typical for seeded/stock imagery) or a local upload path.
*/

if (!function_exists('media_url')) {
    /**
     * Resolve an image column to a usable src.
     *
     * @param  string|null $value    Absolute URL or a path relative to public/.
     * @param  string|null $fallback Returned when $value is empty.
     */
    function media_url(?string $value, ?string $fallback = null): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $fallback;
        }

        return Illuminate\Support\Str::startsWith($value, ['http://', 'https://', 'data:'])
            ? $value
            : asset(ltrim($value, '/'));
    }
}

if (!function_exists('demo_image')) {
    /**
     * Artwork for demo rows only.
     *
     * TEMPORARY — REMOVE BEFORE CODECANYON SUBMISSION.
     * The demo content currently points at Unsplash so the seeded site looks
     * like a real travel agency while the product is being built. That is an
     * external dependency and third-party imagery, neither of which belongs in
     * a package that gets resold, so this goes before submission.
     *
     * Every demo seeder routes through here, so switching back is one edit:
     * replace the body with `return placeholder_image($shape);` and reseed.
     * Nothing else in the codebase needs to change.
     *
     * @param  string|null  $id     Unsplash photo id, or null for the local placeholder
     * @param  string       $shape  Placeholder shape used as the fallback
     */
    function demo_image(?string $id, string $shape = 'card'): string
    {
        $id = trim((string) $id);

        if ($id === '') {
            return placeholder_image($shape);
        }

        // Sized per shape so a slide is not served at avatar dimensions.
        $width = match ($shape) {
            'slide'  => 1920,
            'wide'   => 1200,
            'avatar' => 200,
            default  => 800,
        };

        return "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w={$width}&q=80";
    }
}

if (!function_exists('placeholder_image')) {
    /**
     * Local stand-in artwork for records that have no uploaded image yet.
     * Bundled with the app so demo content never depends on a third-party
     * image service.
     *
     * @param  string $shape wide|card|square|slide|avatar|no-data
     */
    function placeholder_image(string $shape = 'card'): string
    {
        $shapes = ['wide', 'card', 'square', 'slide', 'avatar', 'no-data'];

        if (!in_array($shape, $shapes, true)) {
            $shape = 'card';
        }

        return asset("frontend/images/placeholder/{$shape}.svg");
    }
}

if (!function_exists('initials_avatar')) {
    /**
     * Inline SVG data-URI avatar built from a person's initials. Keeps review
     * cards looking right when a testimonial has no uploaded photo, with no
     * external avatar service involved.
     */
    function initials_avatar(?string $name, int $size = 96): string
    {
        $name = trim((string) $name) ?: '?';

        $initials = collect(preg_split('/\s+/', $name))
            ->filter()
            ->take(2)
            ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))
            ->implode('');

        // Deterministic hue so the same person always gets the same colour.
        $hue      = crc32($name) % 360;
        $fontSize = (int) round($size * 0.42);

        $svg = <<<SVG
        <svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
          <rect width="{$size}" height="{$size}" rx="{$size}" fill="hsl({$hue},62%,46%)"/>
          <text x="50%" y="50%" dy="0.35em" text-anchor="middle" fill="#fff"
                font-family="system-ui,-apple-system,Segoe UI,Roboto,sans-serif"
                font-size="{$fontSize}" font-weight="700">{$initials}</text>
        </svg>
        SVG;

        return 'data:image/svg+xml;base64,' . base64_encode(preg_replace('/\s+/', ' ', $svg));
    }
}

/**
 * User-agent parsing for the Login Activity log.
 *
 * These three were called by LoginActivityRepository but never existed, so
 * every login threw inside its try/catch and the Login Activity page stayed
 * permanently empty. They are deliberately dependency-free: a login must never
 * fail because a parser is missing.
 */
if (! function_exists('UserBrowser')) {
    function UserBrowser($userAgent = null)
    {
        $ua = (string) ($userAgent ?: request()->userAgent());

        // Order matters: Edge and Opera also claim to be Chrome, and Chrome
        // claims to be Safari.
        $browsers = [
            'Edge'              => '/Edg[ea]?\//i',
            'Opera'             => '/OPR\/|Opera/i',
            'Samsung Internet'  => '/SamsungBrowser/i',
            'Chrome'            => '/Chrome|CriOS/i',
            'Firefox'           => '/Firefox|FxiOS/i',
            'Safari'            => '/Safari/i',
            'Internet Explorer' => '/MSIE|Trident/i',
        ];

        foreach ($browsers as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return 'Unknown';
    }
}

if (! function_exists('UserOS')) {
    function UserOS($userAgent = null)
    {
        $ua = (string) ($userAgent ?: request()->userAgent());

        $systems = [
            'Android' => '/Android/i',
            'iOS'     => '/iPhone|iPad|iPod/i',
            'Windows' => '/Windows/i',
            'macOS'   => '/Macintosh|Mac OS X/i',
            'Linux'   => '/Linux|X11/i',
        ];

        foreach ($systems as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return 'Unknown';
    }
}

if (! function_exists('UserDevice')) {
    function UserDevice($userAgent = null)
    {
        $ua = (string) ($userAgent ?: request()->userAgent());

        if (preg_match('/iPad|Tablet|PlayBook|Silk/i', $ua)) {
            return 'Tablet';
        }

        if (preg_match('/Mobile|Android|iPhone|iPod|Windows Phone/i', $ua)) {
            return 'Mobile';
        }

        return 'Desktop';
    }
}

if (!function_exists('mail_password')) {
    /**
     * The SMTP password in plain text.
     *
     * SettingsRepository encrypts this one key on save, so reading it with a
     * bare settings('mail_password') hands Laravel the ciphertext and every
     * mail fails to authenticate the moment an admin saves the mail form.
     * Seeded and hand-inserted values are still plain, so an undecryptable
     * value is returned as-is rather than treated as an error.
     */
    function mail_password(): ?string
    {
        $value = settings('mail_password');

        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return $value;
        }
    }
}

if (!function_exists('decrypt_setting')) {
    /**
     * Read a settings value that SettingsRepository stores encrypted.
     *
     * Same contract as mail_password(): seeded and hand-inserted values are
     * still plain text, so a value that will not decrypt is returned as-is
     * rather than treated as an error. Used for the Gmail API client secret
     * and refresh token, and the mail password.
     */
    function decrypt_setting(string $key): ?string
    {
        $value = settings($key);

        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return decrypt($value);
        } catch (\Illuminate\Contracts\Encryption\DecryptException) {
            return $value;
        }
    }
}

if (!function_exists('currency_symbol')) {
    /**
     * The agency's currency symbol, from Settings → General. Every money
     * figure renders through this instead of a hardcoded '৳', so switching
     * the agency to another currency is a settings change, not a code hunt.
     */
    function currency_symbol(): string
    {
        $symbol = settings('currency_symbol');

        return ($symbol === null || $symbol === '') ? '৳' : $symbol;
    }
}
