<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Http\Middleware\EnsureAppKey;
use App\Models\Backend\Setting;
use App\Models\Customer;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Mobile social sign-in (Phase 2): native credential exchange at
 * POST /api/v1/auth/social/{google,facebook}.
 *
 * Provider calls are faked at the HTTP layer, so every assertion below runs
 * against the real verification + linking code with a controlled provider.
 */
class ApiSocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private const GOOGLE_WEB_ID = 'web-client-id.test.example.com';
    private const GOOGLE_ANDROID_ID = 'android-client-id.test.example.com';
    private const GOOGLE_IOS_ID = 'ios-client-id.test.example.com';
    private const FB_APP_ID = '1234567890';
    private const FB_SECRET = 'fb-test-secret';

    protected function setUp(): void
    {
        parent::setUp();

        // Disable the X-App-Key gate so tests exercise auth, not the gate.
        Setting::updateOrCreate(['key' => EnsureAppKey::SETTING_KEY], ['value' => '']);
        config(['app.api_key' => '']);
        Cache::forget(tenant_cache_prefix().'settings');
    }

    private function enableGoogle(): void
    {
        foreach ([
            'google_client_id' => self::GOOGLE_WEB_ID,
            'google_client_secret' => 'google-test-secret',
            'google_android_client_id' => self::GOOGLE_ANDROID_ID,
            'google_ios_client_id' => self::GOOGLE_IOS_ID,
            'google_status' => Status::ACTIVE->value,
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(tenant_cache_prefix().'settings');
    }

    private function enableFacebook(): void
    {
        foreach ([
            'facebook_client_id' => self::FB_APP_ID,
            'facebook_client_secret' => self::FB_SECRET,
            'facebook_status' => Status::ACTIVE->value,
        ] as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(tenant_cache_prefix().'settings');
    }

    private function disable(string $provider): void
    {
        Setting::updateOrCreate(['key' => "{$provider}_status"], ['value' => Status::INACTIVE->value]);
        Cache::forget(tenant_cache_prefix().'settings');
    }

    private function fakeGoogleTokeninfo(array $overrides = [], int $status = 200): void
    {
        Http::fake([
            '*tokeninfo*' => Http::response(array_merge([
                'iss' => 'https://accounts.google.com',
                'aud' => self::GOOGLE_ANDROID_ID,
                'sub' => 'google-sub-1',
                'email' => 'social.user@example.com',
                'email_verified' => 'true',
                'name' => 'Social User',
                'exp' => time() + 3600,
            ], $overrides), $status),
        ]);
    }

    private function fakeFacebook(string $fbId = 'fb-1', string $email = 'fb.user@example.com', string $name = 'FB User'): void
    {
        Http::fake([
            '*debug_token*' => Http::response(['data' => [
                'app_id' => self::FB_APP_ID,
                'is_valid' => true,
                'expires_at' => time() + 3600,
                'user_id' => $fbId,
            ]], 200),
            '*/me*' => Http::response([
                'id' => $fbId,
                'name' => $name,
                'email' => $email,
            ], 200),
        ]);
    }

    private function customerRole(): Role
    {
        return Role::where('slug', 'customer')->firstOrFail();
    }

    private function makeUser(string $email, ?Role $role = null, array $extra = []): User
    {
        $role ??= $this->customerRole();

        return User::factory()->create(array_merge([
            'email' => $email,
            'role_id' => $role->id,
            'permissions' => $role->permissions ?? [],
            'status' => Status::ACTIVE->value,
        ], $extra));
    }

    /* ---------------- Google ---------------- */

    public function test_google_login_creates_user_customer_and_social_account(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleTokeninfo();

        $response = $this->postJson('/api/v1/auth/social/google', ['id_token' => 'valid-id-token']);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.account.email', 'social.user@example.com')
            ->assertJsonStructure(['data' => ['token', 'account' => ['id', 'account_type', 'name', 'email']]]);

        $this->assertDatabaseHas('social_accounts', ['provider' => 'google', 'provider_id' => 'google-sub-1']);
        $this->assertDatabaseHas('customers', ['email' => 'social.user@example.com']);
        $this->assertDatabaseHas('users', ['email' => 'social.user@example.com']);
    }

    public function test_google_token_authenticates_and_hits_me(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleTokeninfo();

        $token = $this->postJson('/api/v1/auth/social/google', ['id_token' => 'valid-id-token'])
            ->assertOk()
            ->json('data.token');

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.account.email', 'social.user@example.com');
    }

    public function test_google_login_returns_existing_web_linked_account(): void
    {
        $this->enableGoogle();
        $user = $this->makeUser('web.user@example.com');
        SocialAccount::create(['provider' => 'google', 'provider_id' => 'google-sub-1', 'user_id' => $user->id]);
        $this->fakeGoogleTokeninfo(['sub' => 'google-sub-1', 'email' => 'web.user@example.com']);

        $response = $this->postJson('/api/v1/auth/social/google', ['id_token' => 'valid-id-token']);

        $response->assertOk()->assertJsonPath('data.account.id', $user->id);
        $this->assertEquals(1, SocialAccount::where('provider', 'google')->where('provider_id', 'google-sub-1')->count());
    }

    public function test_google_login_reuses_existing_customer_record(): void
    {
        $this->enableGoogle();
        $customer = Customer::create([
            'name' => 'App Customer',
            'email' => 'app.customer@example.com',
            'phone' => '+8801000000001',
            'password' => 'password123',
            'gender' => 'male',
            'status' => 'active',
        ]);
        $this->fakeGoogleTokeninfo(['email' => 'app.customer@example.com', 'sub' => 'google-sub-9']);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'valid-id-token'])->assertOk();

        $this->assertEquals(1, Customer::where('email', 'app.customer@example.com')->count());
        $user = User::where('email', 'app.customer@example.com')->firstOrFail();
        $this->assertEquals($customer->id, $user->customer_id);
    }

    public function test_google_login_rejected_when_provider_disabled(): void
    {
        $this->enableGoogle();
        $this->disable('google');
        $this->fakeGoogleTokeninfo();

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'valid-id-token'])
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseMissing('users', ['email' => 'social.user@example.com']);
    }

    public function test_google_login_rejects_forged_token(): void
    {
        $this->enableGoogle();
        Http::fake(['*tokeninfo*' => Http::response(['error' => 'invalid_token'], 400)]);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'forged'])
            ->assertUnauthorized();

        $this->assertDatabaseMissing('users', ['email' => 'social.user@example.com']);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_google_login_rejects_unverified_email(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleTokeninfo(['email_verified' => 'false']);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'x'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_google_login_rejects_wrong_audience(): void
    {
        $this->enableGoogle();
        $this->fakeGoogleTokeninfo(['aud' => 'attacker-client-id.example.com']);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'x'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_google_login_validates_request(): void
    {
        $this->enableGoogle();

        $this->postJson('/api/v1/auth/social/google', [])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /* ---------------- Facebook ---------------- */

    public function test_facebook_login_success(): void
    {
        $this->enableFacebook();
        $this->fakeFacebook();

        $response = $this->postJson('/api/v1/auth/social/facebook', ['access_token' => 'fb-user-token']);

        $response->assertOk()
            ->assertJsonPath('data.account.email', 'fb.user@example.com')
            ->assertJsonStructure(['data' => ['token', 'account']]);

        $this->assertDatabaseHas('social_accounts', ['provider' => 'facebook', 'provider_id' => 'fb-1']);
    }

    public function test_facebook_login_links_existing_web_account(): void
    {
        $this->enableFacebook();
        $user = $this->makeUser('fb.web@example.com');
        SocialAccount::create(['provider' => 'facebook', 'provider_id' => 'fb-1', 'user_id' => $user->id]);
        $this->fakeFacebook('fb-1', 'fb.web@example.com');

        $this->postJson('/api/v1/auth/social/facebook', ['access_token' => 'fb-user-token'])
            ->assertOk()
            ->assertJsonPath('data.account.id', $user->id);
    }

    public function test_facebook_login_rejected_when_disabled(): void
    {
        $this->enableFacebook();
        $this->disable('facebook');
        $this->fakeFacebook();

        $this->postJson('/api/v1/auth/social/facebook', ['access_token' => 'fb-user-token'])
            ->assertForbidden();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_facebook_login_rejects_invalid_token(): void
    {
        $this->enableFacebook();
        Http::fake([
            '*debug_token*' => Http::response(['data' => ['is_valid' => false, 'app_id' => self::FB_APP_ID]], 200),
        ]);

        $this->postJson('/api/v1/auth/social/facebook', ['access_token' => 'bad'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_facebook_login_rejects_token_for_another_app(): void
    {
        $this->enableFacebook();
        Http::fake([
            '*debug_token*' => Http::response(['data' => [
                'app_id' => '999-other-app',
                'is_valid' => true,
                'expires_at' => time() + 3600,
                'user_id' => 'fb-1',
            ]], 200),
        ]);

        $this->postJson('/api/v1/auth/social/facebook', ['access_token' => 'bad'])
            ->assertUnauthorized();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    /* ---------------- Shared rules ---------------- */

    public function test_agent_gets_agent_account_type(): void
    {
        $this->enableGoogle();
        $agentRole = Role::where('slug', 'agent')->firstOrFail();
        $agent = $this->makeUser('agent.social@example.com', $agentRole);
        $this->fakeGoogleTokeninfo(['email' => 'agent.social@example.com', 'sub' => 'google-agent-1']);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'x'])
            ->assertOk()
            ->assertJsonPath('data.account.account_type', 'agent')
            ->assertJsonPath('data.account.id', $agent->id);
    }

    public function test_admin_accounts_cannot_use_social_login(): void
    {
        $this->enableGoogle();
        $adminRole = new Role();
        $adminRole->name = 'Test Admin';
        $adminRole->slug = 'test-admin';
        $adminRole->permissions = ['dashboard_read'];
        $adminRole->save();
        $this->makeUser('admin.social@example.com', $adminRole);
        $this->fakeGoogleTokeninfo(['email' => 'admin.social@example.com', 'sub' => 'google-admin-1']);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'x'])
            ->assertForbidden();

        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_inactive_accounts_are_rejected(): void
    {
        $this->enableGoogle();
        $this->makeUser('off.social@example.com', null, ['status' => Status::INACTIVE->value]);
        $this->fakeGoogleTokeninfo(['email' => 'off.social@example.com', 'sub' => 'google-off-1']);

        $this->postJson('/api/v1/auth/social/google', ['id_token' => 'x'])
            ->assertForbidden();
    }

    public function test_providers_endpoint_reports_enabled_flags(): void
    {
        $this->enableGoogle();
        $this->disable('facebook');

        $this->getJson('/api/v1/auth/social/providers')
            ->assertOk()
            ->assertJsonPath('data.google', true)
            ->assertJsonPath('data.facebook', false);
    }
}
