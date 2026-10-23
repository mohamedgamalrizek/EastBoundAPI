<?php

namespace Tests\Feature;

use App\Models\User;
use App\Repositories\Role\RoleRepository;
use App\Support\DmcModules;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $response->assertSee('Sales & Lead Campaigns', false);
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
}
