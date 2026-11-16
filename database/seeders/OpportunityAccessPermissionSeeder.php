<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunityAccessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::where('attribute', 'opportunities')->first() ?? new Permission;
            $permission->attribute = 'opportunities';
            $permission->keywords = ['view_assigned' => 'opportunity_read', 'view_all' => 'opportunity_view_all', 'create' => 'opportunity_create', 'update' => 'opportunity_update'];
            $permission->save();
            $managers = ['super-admin', 'admin', 'manager', 'sales-manager', 'eastbound-general-manager', 'eastbound-sales-team-leader'];
            foreach (Role::whereIn('slug', $managers)->get() as $role) {
                $role->permissions = array_values(array_unique(array_merge($role->permissions ?? [], ['opportunity_view_all'])));
                $role->save();
            }
            foreach (User::whereHas('role', fn ($q) => $q->whereIn('slug', $managers))->get() as $user) {
                $user->permissions = array_values(array_unique(array_merge($user->permissions ?? [], ['opportunity_view_all'])));
                $user->save();
            }
        });
    }
}
