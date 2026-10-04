<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Booking\CancellationPolicy;
use App\Traits\ApiReturnFormatTrait;

/**
 * App-wide branding / config for the FLOW mobile app.
 *
 * Values come from the same key-value `settings` table the admin panel writes
 * at /admin/settings/general-settings, so changing the logo there changes the
 * splash screen without an app release. The logo/favicon keys store Upload ids,
 * which logo()/favicon() resolve to absolute URLs.
 */
class SettingsController extends Controller
{
    use ApiReturnFormatTrait;

    public function index()
    {
        return $this->responseWithSuccess('Settings fetched.', [
            'app_name'         => settings('name'),
            'phone'            => settings('phone'),
            'email'            => settings('email'),
            'light_logo'       => logo(settings('light_theme_logo')),
            'dark_logo'        => logo(settings('dark_theme_logo')),
            // App-specific artwork when the agency uploaded it, the website
            // logo otherwise. Resolved here so the app never has to know the
            // fallback rule, and a buyer who ignores the app slots keeps the
            // behaviour they already had.
            'app_logo_light'   => logo(settings('app_logo_light') ?: settings('light_theme_logo')),
            'app_logo_dark'    => logo(settings('app_logo_dark') ?: settings('dark_theme_logo')),
            'favicon'          => favicon(settings('favicon')),
            'default_language' => settings('language') ?? config('app.locale'),
            'currency_symbol'  => settings('currency_symbol'),
            // Booking cancellation policy (Settings > General > Booking Policy)
            // so the app can show the rule before the customer taps cancel.
            'cancellation_window_hours'    => CancellationPolicy::windowHours(),
            'cancellation_penalty_percent' => CancellationPolicy::penaltyPercent(),
        ]);
    }
}
