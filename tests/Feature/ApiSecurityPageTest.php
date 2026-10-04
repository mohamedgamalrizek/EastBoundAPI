<?php
namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSecurityPageTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_the_api_security_page_renders(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('settings.api.security.index'))
            ->assertOk()
            ->assertSee('API Security')
            ->assertSee('app_api_key', false)
            // Generate must ask before it overwrites a live key, and the value
            // must be copyable — it has to be pasted into the app build.
            ->assertSee('generate_app_api_key', false)
            ->assertSee('copy_app_api_key', false)
            ->assertSee('Swal.fire', false);
    }

    public function test_general_settings_no_longer_carries_the_api_security_tab(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $this->actingAs($admin)
            ->get(route('settings.general.index'))
            ->assertOk()
            ->assertDontSee('api-security-settings', false)
            // Demo Login moved out too — the gating is APP_DEMO plus the
            // accounts existing, so there is nothing left to configure.
            ->assertDontSee('auth-demo-settings', false)
            ->assertDontSee('auth_demo_logins_enabled', false);
    }
}
