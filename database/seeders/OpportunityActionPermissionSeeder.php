<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpportunityActionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $permission = Permission::where('attribute', 'opportunities')->first() ?? new Permission;
            $permission->attribute = 'opportunities';
            $permission->keywords = ['view_assigned' => 'opportunity_read', 'view_all' => 'opportunity_view_all',
                'create' => 'opportunity_create', 'update' => 'opportunity_update'];
            $permission->save();
            // Preserve existing CRM action access during this one-time migration.
            foreach ([Role::class, User::class] as $model) {
                foreach ($model::all() as $record) {
                    $permissions = $record->permissions ?? [];
                    foreach (['read', 'create', 'update'] as $action) {
                        if (in_array('crm_'.$action, $permissions, true)) $permissions[] = 'opportunity_'.$action;
                    }
                    $record->permissions = array_values(array_unique($permissions));
                    $record->save();
                }
            }
        });
    }
}
