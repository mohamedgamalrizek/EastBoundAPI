<?php

namespace Tests\Feature;

use App\Models\Backend\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The app can carry its own logo, separate from the website's.
 *
 * The fallback is the part worth pinning: a buyer who never opens the App
 * Branding section must keep getting the website logo, exactly as before.
 */
class AppLogoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function set(string $key, string $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(tenant_cache_prefix() . 'settings');
    }

    public function test_the_app_falls_back_to_the_website_logo_when_no_app_logo_is_set(): void
    {
        $this->set('app_logo_light', '');
        $this->set('app_logo_dark', '');

        $data = $this->getJson('/api/v1/settings')->assertOk()->json('data');

        $this->assertSame($data['light_logo'], $data['app_logo_light']);
        $this->assertSame($data['dark_logo'], $data['app_logo_dark']);
    }

    public function test_an_uploaded_app_logo_overrides_the_website_one(): void
    {
        // The website logos are seeded to upload ids 1 and 2; point the app
        // slots at the favicon upload so the two resolve differently.
        $this->set('app_logo_light', (string) settings('favicon'));
        $this->set('app_logo_dark', (string) settings('favicon'));

        $data = $this->getJson('/api/v1/settings')->assertOk()->json('data');

        $this->assertNotSame($data['light_logo'], $data['app_logo_light']);
        $this->assertNotSame($data['dark_logo'], $data['app_logo_dark']);
        $this->assertSame($data['favicon'], $data['app_logo_light']);
    }
}
