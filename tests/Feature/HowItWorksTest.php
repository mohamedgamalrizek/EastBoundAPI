<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The collapsible "How it works" helper (<x-how-it-works />) should render on
 * admin pages that have content in config/how-it-works.php — both the pages
 * that go through <x-page> and the older hand-rolled tv-card-head layouts —
 * and it must start collapsed (aria-expanded="false").
 */
class HowItWorksTest extends TestCase
{
    // Runs against the dedicated flow_test database (see phpunit.xml).
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_helper_renders_on_x_page_layouts(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('cms.blog.index'));

        $response->assertOk()
            ->assertSee('hiw-toggle', false)
            ->assertSee('aria-expanded="false"', false)
            ->assertSee('How Blogs work');
    }

    public function test_helper_renders_on_old_style_layouts(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('package.index'));

        $response->assertOk()
            ->assertSee('hiw-toggle', false)
            ->assertSee('How Tour Packages work');
    }

    public function test_dot_keys_resolve_flat_not_nested(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('tour.category'));

        $response->assertOk()->assertSee('How Package Categories work');
    }

    public function test_pages_without_content_still_render(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();

        // No config entry matches this route, so the component renders nothing.
        $response = $this->actingAs($admin)->get(route('tour.categoryCreate'));

        $response->assertOk()->assertDontSee('hiw-toggle', false);
    }
}
