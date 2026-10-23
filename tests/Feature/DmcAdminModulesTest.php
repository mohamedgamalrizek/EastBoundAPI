<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\Role\RoleRepository;
use App\Support\AdminMenu;
use App\Support\DmcModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class DmcAdminModulesTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_admin_navigation_keeps_dmc_operations_and_sales_without_removed_modules(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
        $response = $this->actingAs($admin)->get(route('crm.leads'))->assertOk();

        foreach (['insurance.index', 'student-service.index', 'medical-tour.index', 'corporate-travel.index',
            'event-tour.index', 'event-tour.booking.index', 'hajj.index', 'visa.dashboard', 'flight.index',
            'coupon.index', 'review.index', 'newsletter-subscriber.index', 'report.flight',
            'cms.flight-route.index', 'settings.flight-api.index'] as $route) {
            $response->assertDontSee(route($route), false);
        }

        foreach (['hotel.booking.index', 'transport.index', 'tour.guides', 'package.index',
            'campaign.index', 'crm.leads', 'report.leads'] as $route) {
            $response->assertSee(route($route), false);
        }

        $response->assertSee('Campaigns');
        $permissions = app(RoleRepository::class)->permissions()->pluck('attribute')->all();
        $this->assertSame([], array_values(array_intersect(DmcModules::EXCLUDED, $permissions)));
        $this->assertContains('campaign', $permissions);
    }

    public function test_shared_permissions_cannot_reopen_newsletter_flight_reports_or_flight_conversion(): void
    {
        $admin = User::where('email', 'superadmin@bugbuild.com')->firstOrFail();
        $this->actingAs($admin);

        foreach (['newsletter-subscriber.index', 'report.flight', 'cms.flight-route.index',
            'settings.flight-api.index', 'insurance.index', 'medical-tour.index', 'corporate-travel.index',
            'event-tour.booking.index', 'flight.index', 'coupon.index', 'review.index'] as $route) {
            $this->get(route($route))->assertNotFound();
        }
    }

    public function test_menu_sections_follow_the_approved_operating_order(): void
    {
        $this->actingAs(User::where('email', 'superadmin@bugbuild.com')->firstOrFail());

        $this->assertSame([
            'Dashboard', 'CRM & Leads', 'Sales', 'Operations & Trips', 'Projects & Purchase',
            'Finance & Accounting', 'Marketing Analysis (websites)',
            'Websites & Lead Tracking Integration', 'System & Administration',
        ], array_column(AdminMenu::sections(), 'label'));
    }

    public function test_sidebar_renders_individual_icons_including_nested_items(): void
    {
        $this->actingAs(User::where('email', 'superadmin@bugbuild.com')->firstOrFail());
        $sidebar = view('backend.partials.sidebar')->render();

        $this->assertStringContainsString('class="fa-solid fa-bullseye"', $sidebar);
        $this->assertStringContainsString('class="fa-solid fa-passport"', $sidebar);
        $this->assertStringContainsString('class="fa-solid fa-bed"', $sidebar);
        $this->assertStringContainsString('class="fa-brands fa-whatsapp"', $sidebar);
        preg_match_all('/<i class="([^"]+)" aria-hidden="true"><\/i>/', $sidebar, $matches);
        $this->assertGreaterThan(100, count($matches[1]));
        $this->assertCount(count($matches[1]), array_unique($matches[1]));
    }

    public function test_enabled_workflows_have_live_links_and_no_next_phase_placeholders(): void
    {
        $this->actingAs(User::where('email', 'superadmin@bugbuild.com')->firstOrFail());
        $sections = collect(AdminMenu::sections());
        $sales = $sections->firstWhere('label', 'Sales');

        foreach (['All Opportunities', 'Quotations', 'Pending Approval', 'Sales Orders', 'Confirmed Trips'] as $label) {
            $item = collect($sales['items'])->firstWhere('label', $label);
            $this->assertFalse($item['pending']);
            $this->assertArrayHasKey('route', $item);
        }

        $html = view('backend.partials.sidebar')->render();
        $this->assertStringNotContainsString('aria-disabled="true"', $html);
        $this->assertStringNotContainsString('Next phase', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_accounting_only_staff_do_not_see_other_departments(): void
    {
        auth()->setUser((new User)->forceFill(['permissions' => ['accounting_read']]));
        $sections = AdminMenu::sections();

        $this->assertSame(['Finance & Accounting', 'System & Administration'], array_column($sections, 'label'));
        $finance = $sections[0];
        $this->assertContains('Customer Invoices', array_column($finance['items'], 'label'));
        $this->assertContains('Vendor Bills', array_column($finance['items'], 'label'));

        auth()->setUser((new User)->forceFill(['permissions' => []]));
        $this->assertSame([], AdminMenu::sections());
    }

    public function test_active_edit_routes_open_the_correct_parent_menu(): void
    {
        auth()->setUser((new User)->forceFill(['permissions' => ['user_read']]));
        $route = Route::getRoutes()->getByName('user.edit');
        request()->setRouteResolver(fn () => $route);
        $sections = AdminMenu::sections();
        $users = $sections[0]['items'][0];

        $this->assertSame('User Roles & Permissions', $users['label']);
        $this->assertTrue($users['active']);
        $this->assertTrue($users['children'][0]['current']);
    }

    public function test_every_configured_menu_link_exists_and_respects_module_exclusions(): void
    {
        $checkItems = function (array $items) use (&$checkItems): void {
            foreach ($items as $item) {
                if (isset($item['route'])) {
                    $this->assertTrue(Route::has($item['route']), $item['route']);
                    $this->assertTrue(DmcModules::routeEnabled($item['route']), $item['route']);
                }
                $checkItems($item['children'] ?? []);
            }
        };

        foreach (config('admin-menu') as $section) {
            $checkItems($section['items']);
        }
    }
}
