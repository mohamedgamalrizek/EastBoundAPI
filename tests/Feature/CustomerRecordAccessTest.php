<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Dmc\Opportunity;
use App\Models\Dmc\Quotation;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerRecordAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'activitylog.enabled' => false]);
        foreach (['customers', 'leads'] as $name) {
            Schema::create($name, function (Blueprint $table) use ($name) {
                $table->id();
                $table->integer('assigned_to')->nullable();
                if ($name === 'leads') $table->softDeletes();
            });
            DB::table($name)->insert([['id' => 1, 'assigned_to' => 10], ['id' => 2, 'assigned_to' => 20], ['id' => 3, 'assigned_to' => null]]);
        }
        Schema::create('dmc_opportunities', function (Blueprint $table) {
            $table->id(); $table->integer('assigned_to')->nullable(); $table->integer('lead_id')->nullable(); $table->integer('customer_id');
        });
        Schema::create('dmc_quotations', function (Blueprint $table) {
            $table->id(); $table->integer('opportunity_id');
        });
        foreach ([1 => [1, 1], 2 => [2, 1], 3 => [1, 2], 4 => [null, 1]] as $id => [$lead, $customer]) {
            DB::table('dmc_opportunities')->insert(['id' => $id, 'assigned_to' => 10, 'lead_id' => $lead, 'customer_id' => $customer]);
            DB::table('dmc_quotations')->insert(['id' => $id, 'opportunity_id' => $id]);
        }
        $user = new User;
        $user->id = 10;
        $user->permissions = ['dashboard_read', 'customer_read', 'lead_read'];
        $this->actingAs($user);
    }

    public function test_opportunities_and_children_follow_lead_ownership_independently(): void
    {
        $this->assertSame([1], Customer::pluck('id')->all());
        $this->assertSame([1, 3, 4], Opportunity::pluck('id')->all());
        $this->assertSame([1, 3, 4], Quotation::pluck('id')->all());
        $this->assertNull(Quotation::find(2));
        $this->assertSame(0, Opportunity::whereKey(2)->delete());
    }

    public function test_view_all_permissions_are_independent(): void
    {
        auth()->user()->permissions = ['customer_view_all'];
        $this->assertSame(3, Customer::count());
        $this->assertSame([1, 3, 4], Opportunity::pluck('id')->all());
        auth()->user()->permissions = ['lead_view_all'];
        $this->assertSame([1, 3, 4], Opportunity::pluck('id')->all());
        auth()->user()->permissions = ['opportunity_view_all'];
        $this->assertSame(4, Quotation::count());
        $this->assertFalse(auth()->user()->can_access('customer_delete'));
    }

    public function test_reassignment_immediately_changes_child_visibility(): void
    {
        DB::table('leads')->where('id', 1)->update(['assigned_to' => 20]);
        $this->assertSame([4], Quotation::pluck('id')->all());
    }

    public function test_submitted_hidden_parent_is_rejected(): void
    {
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        Opportunity::create(['lead_id' => 2, 'customer_id' => 1]);
    }

    public function test_portal_keeps_its_existing_customer_access(): void
    {
        auth()->user()->permissions = ['customer_portal_read'];
        $this->assertSame(3, Customer::count());
    }
    public function test_new_customer_defaults_to_creator_but_explicit_assignment_is_preserved(): void
    {
        $customer = new Customer;
        $customer->timestamps = false;
        $customer->save();
        $this->assertSame(10, $customer->assigned_to);

        $unassigned = new Customer(['assigned_to' => null]);
        $unassigned->timestamps = false;
        $unassigned->save();
        $this->assertNull($unassigned->assigned_to);
    }

    public function test_internal_user_cannot_bypass_scope_with_a_portal_permission(): void
    {
        auth()->user()->permissions = ['dashboard_read', 'customer_portal_read'];
        $this->assertSame([1], Customer::pluck('id')->all());
    }

    public function test_lead_and_customer_view_all_do_not_grant_all_opportunities(): void
    {
        auth()->user()->permissions = ['customer_view_all', 'lead_view_all'];
        $this->assertSame([1, 3, 4], Opportunity::pluck('id')->all());
        $this->assertNull(Quotation::find(2));
        auth()->user()->permissions = ['opportunity_view_all'];
        $this->assertSame(4, Opportunity::count());
        $this->assertSame([1], Customer::pluck('id')->all());
        $this->assertFalse(auth()->user()->can_access('dmc_sales_update'));
    }

    public function test_opportunity_menu_and_routes_use_independent_access_permissions(): void
    {
        $visible = fn () => collect(\App\Support\AdminMenu::sections())->pluck('items')->flatten(1)
            ->contains('route', 'dmc.opportunities.index');
        auth()->user()->permissions = ['dashboard_read'];
        $this->assertFalse($visible());
        $this->assertFalse(auth()->user()->can_access('opportunity_read'));
        foreach (['opportunity_read', 'opportunity_view_all'] as $permission) {
            auth()->user()->permissions = ['dashboard_read', $permission];
            $this->assertTrue($visible());
            $this->assertTrue(auth()->user()->can_access('opportunity_read'));
            $this->assertFalse(auth()->user()->can_access('opportunity_update'));
            $this->assertFalse(auth()->user()->can_access('crm_read'));
        }
        foreach (['index' => 'read', 'show' => 'read', 'store' => 'create', 'update' => 'update'] as $route => $action) {
            $this->assertContains('hasPermission:opportunity_'.$action,
                app('router')->getRoutes()->getByName('dmc.opportunities.'.$route)->gatherMiddleware());
        }
    }

}
