<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureAppKey;
use App\Models\Backend\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The shared X-App-Key gate on the mobile API.
 *
 * The behaviour that matters most is the first test: with no key configured
 * the check must be completely inert. Every existing install and every fresh
 * buyer sits in that state, so a regression there breaks the app for everyone
 * at once rather than for anybody who opted in.
 */
class AppApiKeyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function setKey(string $value): void
    {
        Setting::updateOrCreate(['key' => EnsureAppKey::SETTING_KEY], ['value' => $value]);
        Cache::forget(tenant_cache_prefix() . 'settings');
        config(['app.api_key' => '']);
    }

    public function test_no_key_configured_lets_every_caller_through(): void
    {
        $this->setKey('');

        $this->getJson('/api/v1/home')->assertOk();
    }

    public function test_a_caller_without_the_key_is_refused_once_one_is_set(): void
    {
        $this->setKey('flow-test-key');

        $this->getJson('/api/v1/home')->assertUnauthorized();
    }

    public function test_the_right_key_is_accepted(): void
    {
        $this->setKey('flow-test-key');

        $this->withHeader('X-App-Key', 'flow-test-key')
            ->getJson('/api/v1/home')
            ->assertOk();
    }

    public function test_a_wrong_key_is_refused(): void
    {
        $this->setKey('flow-test-key');

        $this->withHeader('X-App-Key', 'not-the-key')
            ->getJson('/api/v1/home')
            ->assertUnauthorized();
    }

    public function test_our_own_browser_frontend_is_exempt(): void
    {
        $this->setKey('flow-test-key');

        // A browser cannot hold a secret header; same-origin requests are
        // covered by CORS and the session instead. The origin is derived from
        // the app's own URL rather than hardcoded, so the test does not break
        // when APP_URL differs.
        $this->withHeader('Origin', (string) config('app.url'))
            ->getJson('/api/v1/home')
            ->assertOk();
    }

    public function test_a_foreign_origin_still_needs_the_key(): void
    {
        $this->setKey('flow-test-key');

        $this->withHeader('Origin', 'https://scraper.example.com')
            ->getJson('/api/v1/home')
            ->assertUnauthorized();
    }
}
