<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\User;
use App\Repositories\Lead\LeadRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LeadAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:', 'activitylog.enabled' => false]);
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('assigned_to')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::table('leads')->insert([
            ['id' => 1, 'name' => 'Mine', 'assigned_to' => 10, 'deleted_at' => null],
            ['id' => 2, 'name' => 'Other', 'assigned_to' => 20, 'deleted_at' => null],
            ['id' => 3, 'name' => 'Unassigned', 'assigned_to' => null, 'deleted_at' => null],
            ['id' => 4, 'name' => 'Deleted mine', 'assigned_to' => 10, 'deleted_at' => now()],
            ['id' => 5, 'name' => 'Deleted other', 'assigned_to' => 20, 'deleted_at' => now()],
        ]);
    }

    public function test_sales_can_only_query_their_assigned_records_including_trash(): void
    {
        $user = new User;
        $user->id = 10;
        $user->permissions = ['lead_read', 'lead_update', 'lead_delete'];
        $this->actingAs($user);
        $this->assertSame([1], Lead::pluck('id')->all());
        $this->assertSame([4], Lead::onlyTrashed()->pluck('id')->all());
        $this->assertNull((new LeadRepository(new Lead))->get(2));
        $this->assertSame(0, Lead::whereKey(2)->delete());
        $this->assertSame(0, Lead::onlyTrashed()->whereKey(5)->restore());
        $this->assertFalse((new LeadRepository(new Lead))->forceDelete(5)['status']);
        $this->assertTrue(DB::table('leads')->where('id', 5)->exists());
        $this->assertSame(0, Lead::where('assigned_to', 20)->count());
    }

    public function test_lead_routes_require_independent_action_permissions(): void
    {
        foreach (['crm.leads' => 'read', 'crm.leads.store' => 'create', 'crm.leads.update' => 'update',
            'crm.leads.delete' => 'delete', 'crm.deleted-leads.restore' => 'restore', 'crm.deleted-leads.destroy' => 'force_delete'] as $name => $permission) {
            $route = app('router')->getRoutes()->getByName($name);
            $this->assertContains('hasPermission:lead_'.$permission, $route->gatherMiddleware());
        }
    }

    public function test_assignment_updates_owner_and_visibility_and_rejects_inactive_users(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('owner')->nullable();
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('status');
            $table->json('permissions');
        });
        foreach ([20 => 1, 30 => 0] as $id => $status) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Sales '.$id, 'status' => $status,
                'permissions' => json_encode(['dashboard_read', 'lead_read'])]);
        }
        $manager = new User;
        $manager->id = 10;
        $manager->permissions = ['lead_read', 'lead_view_all', 'lead_assign'];
        $this->actingAs($manager);
        $repo = new LeadRepository(new Lead);
        $lead = $repo->assignTo(1, 20);
        $this->assertSame(20, $lead->assigned_to);
        $this->assertSame('Sales 20', $lead->owner);
        $this->assertContains('hasPermission:lead_assign', app('router')->getRoutes()->getByName('crm.leads.assign')->gatherMiddleware());
        $manager->permissions = ['lead_read'];
        $this->assertNull($repo->get(1));
        $manager->permissions = ['lead_read', 'lead_view_all', 'lead_assign'];
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $repo->assignTo(1, 30);
    }

    public function test_view_all_is_permission_based_and_does_not_grant_delete(): void
    {
        $user = new User;
        $user->id = 10;
        $user->permissions = ['lead_read', 'lead_view_all'];
        $this->actingAs($user);
        $this->assertSame(3, Lead::count());
        $this->assertSame(2, Lead::onlyTrashed()->count());
        $this->assertFalse($user->can_access('lead_delete'));
        $this->assertFalse($user->can_access('lead_force_delete'));
        $user->permissions = ['lead_read'];
        $this->assertSame(1, Lead::count());
    }
}
