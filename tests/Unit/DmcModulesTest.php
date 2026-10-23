<?php

namespace Tests\Unit;

use App\Http\Middleware\EnsurePermission;
use App\Models\User;
use App\Support\DmcModules;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\HttpException;

class DmcModulesTest extends TestCase
{
    public function test_excluded_permissions_remain_disabled_when_assigned_to_a_user(): void
    {
        $user = new User();
        $user->permissions = ['visa_read', 'hajj_create', 'student_service_update', 'tour_read'];

        foreach (DmcModules::EXCLUDED as $module) {
            foreach (['read', 'create', 'update', 'delete'] as $action) {
                $this->assertFalse(DmcModules::permissionEnabled($module.'_'.$action));
                $this->assertFalse($user->can_access($module.'_'.$action));
            }
        }

        $this->assertTrue($user->can_access('tour_read'));
    }

    public function test_dmc_operations_and_support_permissions_remain_enabled(): void
    {
        foreach (['tour_read', 'hotel_read', 'transport_read', 'flight_read', 'crm_read',
            'accounting_read', 'corporate_travel_read', 'event_tour_read', 'insurance_read',
            'user_read', 'role_read', 'report_read', 'cms_read'] as $permission) {
            $this->assertTrue(DmcModules::permissionEnabled($permission));
        }
    }

    public function test_related_admin_routes_are_disabled_under_shared_permissions(): void
    {
        foreach (['visa.dashboard', 'visa.application.store', 'hajj.index', 'hajj.pilgrim.create',
            'student-service.store', 'report.visa', 'sup.visa', 'cms.visa-service.update'] as $route) {
            $this->assertFalse(DmcModules::routeEnabled($route));
        }

        foreach (['dashboard', 'crm.leads', 'report.sales', 'sup.hotels', 'cms.page.index'] as $route) {
            $this->assertTrue(DmcModules::routeEnabled($route));
        }
    }

    public function test_middleware_blocks_direct_access_before_checking_user_permissions(): void
    {
        $previousApp = Application::getInstance();
        new Application();

        try {
            foreach (['visa.dashboard' => 'visa_read', 'hajj.index' => 'hajj_read',
                'student-service.store' => 'student_service_create', 'report.visa' => 'report_read',
                'sup.visa' => 'supplier_read', 'cms.visa-service.store' => 'cms_create'] as $name => $permission) {
                $route = (new Route('GET', '/', fn () => null))->name($name);
                $request = Request::create('/');
                $request->setRouteResolver(fn () => $route);

                try {
                    (new EnsurePermission())->handle($request, function () {
                        $this->fail('Excluded modules must not reach their controllers.');
                    }, $permission);
                    $this->fail('Excluded modules must return 404.');
                } catch (HttpException $exception) {
                    $this->assertSame(404, $exception->getStatusCode());
                }
            }
        } finally {
            Application::setInstance($previousApp);
        }
    }
}
