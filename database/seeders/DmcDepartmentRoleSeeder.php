<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DmcDepartmentRoleSeeder extends Seeder
{
    public function run(): void
    {
        $self = ['dashboard_read', 'profile_read', 'profile_update', 'password_update'];
        $sales = ['crm_read', 'crm_create', 'crm_update', 'customer_read', 'customer_create', 'customer_update',
            'dmc_sales_read', 'dmc_sales_create', 'dmc_sales_update', 'dmc_operations_read'];
        $operations = ['dmc_operations_read', 'dmc_operations_create', 'dmc_operations_update',
            'dmc_procurement_read', 'dmc_procurement_create', 'dmc_procurement_update',
            'tour_read', 'hotel_read', 'transport_read', 'task_read', 'task_create', 'task_update', 'customer_read', 'supplier_read', 'supplier_create', 'supplier_update'];
        $templates = [
            'Sales' => $sales,
            'Sales Team Leader' => array_merge($sales, ['dmc_sales_approve_tl']),
            'General Manager' => array_merge($sales, ['dmc_sales_approve_tl', 'dmc_sales_approve_gm', 'dmc_operations_read',
                'dmc_procurement_read', 'accounting_read', 'supplier_read', 'task_read', 'campaign_read', 'dmc_marketing_read', 'report_read']),
            'Operations' => $operations,
            'Accounting' => ['accounting_read', 'accounting_create', 'accounting_update', 'supplier_read', 'supplier_update', 'dmc_procurement_read', 'report_read'],
            'Marketing' => ['crm_read', 'campaign_read', 'campaign_create', 'campaign_update', 'dmc_marketing_read'],
        ];
        foreach ($templates as $name => $permissions) {
            $slug = 'eastbound-'.Str::slug($name);
            if (Role::where('slug', $slug)->exists()) {
                continue;
            }
            $role = new Role;
            $role->name = 'Eastbound '.$name.' (Template)';
            $role->slug = $slug;
            $role->permissions = array_values(array_unique(array_merge($self, $permissions)));
            $role->status = 1;
            $role->save();
        }
    }
}
