<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Idempotent: keyed on attribute so the seeder can be re-run to pick up
        // newly added module permissions without creating duplicate rows.
        // (Permission has no $fillable, so attributes are set explicitly.)
        foreach ($this->permissions() as $key => $keywords) {
            $permission            = Permission::where('attribute', $key)->first() ?? new Permission();
            $permission->attribute = $key;
            $permission->keywords  = $keywords;
            $permission->save();
        }
    }

    private function permissions(): array
    {
        return [

            'dashboard'         => ['read' => 'dashboard_read',],
            'users'             => ['read' => 'user_read', 'create' => 'user_create', 'update' => 'user_update', 'delete' => 'user_delete', 'permission_update' => 'permission_update'],
            'roles'             => ['read' =>  'role_read', 'create' => 'role_create', 'update' =>  'role_update', 'delete' =>  'role_delete'],
            'language'          => ['read' =>  'language_read', 'create' => 'language_create', 'update' =>  'language_update', 'delete' =>  'language_delete', 'phrase' =>  'language_phrase_update'],
            'general_settings'  => ['read' =>  'general_settings_read', 'update' => 'general_settings_update'],
            'storage_settings'  => ['read' =>  'storage_settings_read', 'update' => 'storage_settings_update'],
            'recaptcha_settings' => ['read' =>  'recaptcha_settings_read', 'update' => 'recaptcha_settings_update'],
            'mail_settings'     => ['read' =>  'mail_settings_read', 'update' => 'mail_settings_update'],
            'sms_settings'      => ['read' =>  'sms_settings_read', 'update' => 'sms_settings_update'],
            'payment_settings'  => ['read' =>  'payment_settings_read', 'update' => 'payment_settings_update'],
            'profile'           => ['read' => 'profile_read', 'update' => 'profile_update'],
            'account_security'  => [
                'password_update'         => 'password_update',
                'email_update'            => 'email_update',
                'phone_update'            => 'phone_update',
                'self_user_account_delete' => 'self_user_account_delete',
            ],
            'todo'              => ['read' =>  'todo_read', 'create' => 'todo_create', 'update' => 'todo_update', 'delete' => 'todo_delete'],
            'activity_logs'     => ['read' => 'activity_logs_read', 'view' => 'activity_logs_view', 'login_activity_read' => 'login_activity_read'],

            'route'             => ['read' => 'route_read', 'search' => 'route_search',],

            // ---- Travel ERP modules ----
            'packages'          => ['read' => 'package_read', 'create' => 'package_create', 'update' => 'package_update', 'delete' => 'package_delete'],
            'bookings'          => ['read' => 'booking_read', 'create' => 'booking_create', 'update' => 'booking_update', 'delete' => 'booking_delete'],
            'customers'         => ['read' => 'customer_read', 'create' => 'customer_create', 'update' => 'customer_update', 'delete' => 'customer_delete'],
            'crm'               => ['read' => 'crm_read', 'create' => 'crm_create', 'update' => 'crm_update', 'delete' => 'crm_delete'],
            'visa'              => ['read' => 'visa_read', 'create' => 'visa_create', 'update' => 'visa_update', 'delete' => 'visa_delete'],
            'tour'              => ['read' => 'tour_read', 'create' => 'tour_create', 'update' => 'tour_update', 'delete' => 'tour_delete'],
            'hajj'              => ['read' => 'hajj_read', 'create' => 'hajj_create', 'update' => 'hajj_update', 'delete' => 'hajj_delete'],
            'hotel'             => ['read' => 'hotel_read', 'create' => 'hotel_create', 'update' => 'hotel_update', 'delete' => 'hotel_delete'],
            'transport'         => ['read' => 'transport_read', 'create' => 'transport_create', 'update' => 'transport_update', 'delete' => 'transport_delete'],
            'flight'            => ['read' => 'flight_read', 'create' => 'flight_create', 'update' => 'flight_update', 'delete' => 'flight_delete'],
            'accounting'        => ['read' => 'accounting_read', 'create' => 'accounting_create', 'update' => 'accounting_update', 'delete' => 'accounting_delete'],
            'supplier'          => ['read' => 'supplier_read', 'create' => 'supplier_create', 'update' => 'supplier_update', 'delete' => 'supplier_delete'],
            'task'              => ['read' => 'task_read', 'create' => 'task_create', 'update' => 'task_update', 'delete' => 'task_delete'],
            'support'           => ['read' => 'support_read', 'create' => 'support_create', 'update' => 'support_update', 'delete' => 'support_delete'],
            'report'            => ['read' => 'report_read'],
            'cms'               => ['read' => 'cms_read', 'create' => 'cms_create', 'update' => 'cms_update', 'delete' => 'cms_delete'],
            'hr'                => ['read' => 'hr_read', 'create' => 'hr_create', 'update' => 'hr_update', 'delete' => 'hr_delete'],
            'agent_finance'     => ['read' => 'agent_finance_read', 'create' => 'agent_finance_create', 'update' => 'agent_finance_update', 'delete' => 'agent_finance_delete'],
            'customer_portal'   => ['read' => 'customer_portal_read'],
            'agent_portal'      => ['read' => 'agent_portal_read'],
            'staff_portal'      => ['read' => 'staff_portal_read'],
            'saas'              => ['read' => 'saas_read'],

            "coupon" => ["read" => "coupon_read", "create" => "coupon_create", "update" => "coupon_update", "delete" => "coupon_delete"],
            "campaign" => ["read" => "campaign_read", "create" => "campaign_create", "update" => "campaign_update", "delete" => "campaign_delete"],
            "branch" => ["read" => "branch_read", "create" => "branch_create", "update" => "branch_update", "delete" => "branch_delete"],
            "insurance" => ["read" => "insurance_read", "create" => "insurance_create", "update" => "insurance_update", "delete" => "insurance_delete"],
            "student_service" => ["read" => "student_service_read", "create" => "student_service_create", "update" => "student_service_update", "delete" => "student_service_delete"],
            "medical_tour" => ["read" => "medical_tour_read", "create" => "medical_tour_create", "update" => "medical_tour_update", "delete" => "medical_tour_delete"],
            "corporate_travel" => ["read" => "corporate_travel_read", "create" => "corporate_travel_create", "update" => "corporate_travel_update", "delete" => "corporate_travel_delete"],
            "event_tour" => ["read" => "event_tour_read", "create" => "event_tour_create", "update" => "event_tour_update", "delete" => "event_tour_delete"],
            // No create: a review is written by the customer who took the trip.
            "review" => ["read" => "review_read", "update" => "review_update", "delete" => "review_delete"],

        ];
    }
}
