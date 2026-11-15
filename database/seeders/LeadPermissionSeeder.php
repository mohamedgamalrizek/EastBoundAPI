<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeadPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $keywords = [];
            foreach (['read', 'create', 'update', 'delete', 'view_all', 'assign', 'restore', 'force_delete'] as $action) {
                $keywords[$action] = 'lead_'.$action;
            }
            $permission = Permission::where('attribute', 'leads')->first() ?? new Permission;
            $permission->attribute = 'leads';
            $permission->keywords = $keywords;
            $permission->save();
            $expand = function (array $permissions, string $slug) use ($keywords): array {
                foreach (['read', 'create', 'update', 'delete'] as $action) {
                    if (in_array('crm_'.$action, $permissions, true)) $permissions[] = 'lead_'.$action;
                }
                if (in_array($slug, ['super-admin', 'admin', 'manager', 'eastbound-general-manager', 'eastbound-sales-team-leader'], true)) {
                    $permissions = array_merge($permissions, array_values($keywords));
                }
                return array_values(array_unique($permissions));
            };
            foreach (Role::all() as $role) {
                $role->permissions = $expand($role->permissions ?? [], $role->slug);
                $role->save();
            }
            foreach (User::with('role')->get() as $user) {
                $user->permissions = $expand($user->permissions ?? [], $user->role?->slug ?? '');
                $user->save();
            }
            foreach (['sales-manager' => array_values($keywords), 'sales' => ['lead_read', 'lead_create', 'lead_update']] as $slug => $permissions) {
                if (Role::where('slug', $slug)->exists()) continue;
                $role = new Role;
                $role->name = $slug === 'sales-manager' ? 'Sales Manager' : 'Sales';
                $role->slug = $slug;
                $role->status = 1;
                $role->permissions = array_merge(['dashboard_read', 'profile_read', 'profile_update', 'password_update'], $permissions);
                $role->save();
            }
            // Only unambiguous legacy names may become account assignments.
            foreach (DB::table('leads')->whereNull('assigned_to')->whereNotNull('owner')->distinct()->pluck('owner') as $owner) {
                $ids = DB::table('users')->where('name', $owner)->pluck('id');
                if ($ids->count() === 1) DB::table('leads')->whereNull('assigned_to')->where('owner', $owner)->update(['assigned_to' => $ids->first()]);
            }
        });
    }
}
