<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Breadcrumbs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class BreadcrumbsTest extends TestCase
{
    private function page(string $name, array $permissions = []): void
    {
        $user = new User;
        $user->id = 1;
        $user->permissions = $permissions;
        $this->actingAs($user);
        $request = Request::create('/');
        $request->setRouteResolver(fn () => app('router')->getRoutes()->getByName($name));
        $this->app->instance('request', $request);
    }

    public function test_lead_edit_links_to_list_and_has_one_current_page(): void
    {
        $this->page('crm.leads.edit', ['dashboard_read', 'lead_read']);
        $trail = Breadcrumbs::build(['CRM', 'Leads', 'Edit'], 'Edit Lead');
        $this->assertSame(['label' => 'Edit', 'url' => null], end($trail));
        $this->assertContains(route('crm.leads'), array_column($trail, 'url'));
        $html = Blade::render('<nav aria-label="breadcrumb"><x-breadcrumbs :items="[\'CRM\', \'Leads\', \'Edit\']" /></nav>');
        $this->assertSame(1, substr_count($html, 'aria-current="page"'));
        $this->assertStringNotContainsString('href=""', $html);
        $this->assertStringNotContainsString('href="#"', $html);
    }

    public function test_links_require_permission_and_existing_get_route_without_missing_ids(): void
    {
        $this->page('crm.leads.edit', ['lead_update']);
        $this->assertNull(Breadcrumbs::url('crm.leads'));
        $this->assertNull(Breadcrumbs::url('does.not.exist'));
        $this->assertNull(Breadcrumbs::url('crm.leads.delete'));
        $this->assertNull(Breadcrumbs::url('crm.leads.show'));
    }

    public function test_dmc_create_has_action_and_dashboard_is_not_duplicated(): void
    {
        $this->page('dmc.quotations.create', ['dashboard_read', 'dmc_sales_read']);
        $trail = Breadcrumbs::build(['DMC', 'Quotations'], 'Quotations');
        $this->assertSame('Create', end($trail)['label']);
        $this->assertContains(route('dmc.quotations.index'), array_column($trail, 'url'));
        $this->page('dashboard', ['dashboard_read']);
        $this->assertSame([['label' => 'Dashboard', 'url' => null]], Breadcrumbs::build());
    }

    public function test_every_named_get_route_can_build_a_trail_without_fake_links(): void
    {
        foreach (app('router')->getRoutes() as $route) {
            if (! $route->getName() || ! in_array('GET', $route->methods())) continue;
            $this->page($route->getName());
            $trail = Breadcrumbs::build(['Section', 'Current'], 'Current');
            $this->assertNull(end($trail)['url'], $route->getName());
            foreach ($trail as $crumb) {
                $this->assertNotSame('', $crumb['url']);
                $this->assertNotSame('#', $crumb['url']);
            }
        }
    }
}
