<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerAccessPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::where('attribute', 'customers')->first() ?? new Permission;
            $permission->attribute = 'customers';
            $permission->keywords = array_merge($permission->keywords ?? [], [
                'read' => 'customer_read', 'create' => 'customer_create', 'update' => 'customer_update',
                'delete' => 'customer_delete', 'view_all' => 'customer_view_all', 'assign' => 'customer_assign',
            ]);
            $permission->save();
            $managers = ['super-admin', 'admin', 'manager', 'sales-manager', 'eastbound-general-manager', 'eastbound-sales-team-leader'];
            foreach (Role::whereIn('slug', $managers)->get() as $role) {
                $role->permissions = array_values(array_unique(array_merge($role->permissions ?? [], ['customer_view_all', 'customer_assign'])));
                $role->save();
            }
            foreach (User::whereHas('role', fn ($q) => $q->whereIn('slug', $managers))->get() as $user) {
                $user->permissions = array_values(array_unique(array_merge($user->permissions ?? [], ['customer_view_all', 'customer_assign'])));
                $user->save();
            }
        });
    }
}
