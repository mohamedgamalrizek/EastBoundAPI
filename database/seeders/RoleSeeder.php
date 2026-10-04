<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        /**
         * Role list. Order matters — role_id is assigned in this sequence
         * (Super Admin = 1, Admin = 2, ...). UserSeeder references these ids.
         *
         * Admin-type roles (log in through the admin panel):
         *   Super Admin, Admin, Manager, Operations Staff, Accountant, Support Agent
         * Portal-type roles (log in through the public/portal login):
         *   Agent, Customer
         */
        $roles = [
            'Super Admin'      => $this->superAdminPermissions(),
            'Admin'            => $this->adminPermissions(),
            'Manager'          => $this->managerPermissions(),
            'Operations Staff' => $this->operationsPermissions(),
            'Accountant'       => $this->accountantPermissions(),
            'Support Agent'    => $this->supportAgentPermissions(),
            'Agent'            => $this->agentPermissions(),
            'Customer'         => $this->customerPermissions(),
            'SaaS Super Admin' => $this->saasSuperAdminPermissions(),
            'Staff'            => $this->staffPermissions(),
            'Tour Guide'       => $this->tourGuidePermissions(),
        ];

        // Idempotent: keyed on slug so the seeder can be re-run to refresh
        // permission sets (e.g. after a module gains CRUD) without duplicating
        // roles or shifting the role_id sequence that UserSeeder relies on.
        foreach ($roles as $name => $permissions) {
            $slug              = str_replace(' ', '-', strtolower($name));
            $role              = Role::where('slug', $slug)->first() ?? new Role();
            $role->name        = $name;
            $role->slug        = $slug;
            $role->permissions = $permissions;
            $role->save();
        }
    }

    /**
     * Permissions every authenticated user needs to manage their own account.
     */
    private function selfPermissions(): array
    {
        return [
            'profile_read',
            'profile_update',
            'self_user_account_delete',
            'email_update',
            'phone_update',
            'password_update',
        ];
    }

    /**
     * Super Admin — full, unrestricted access including SaaS and every portal.
     */
    private function superAdminPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'dashboard_read',

            'user_read', 'user_create', 'user_update', 'user_delete', 'permission_update',
            'role_read', 'role_create', 'role_update', 'role_delete',

            'language_read', 'language_create', 'language_update', 'language_delete', 'language_phrase_update',

            'general_settings_read', 'general_settings_update',
            'mail_settings_read', 'mail_settings_update',
            'sms_settings_read', 'sms_settings_update',
            'payment_settings_read', 'payment_settings_update',
            'storage_settings_read', 'storage_settings_update',
            'recaptcha_settings_read', 'recaptcha_settings_update',

            'todo_read', 'todo_create', 'todo_update', 'todo_delete',

            'activity_logs_read', 'activity_logs_view', 'login_activity_read',
            'route_read', 'route_search',

            // ---- Travel ERP modules ----
            'package_read', 'package_create', 'package_update', 'package_delete',
            'booking_read', 'booking_create', 'booking_update', 'booking_delete',
            'customer_read', 'customer_create', 'customer_update', 'customer_delete',
            'crm_read', 'crm_create', 'crm_update', 'crm_delete',
            'visa_read', 'visa_create', 'visa_update', 'visa_delete',
            'tour_read', 'tour_create', 'tour_update', 'tour_delete',
            'hajj_read', 'hajj_create', 'hajj_update', 'hajj_delete',
            'hotel_read', 'hotel_create', 'hotel_update', 'hotel_delete',
            'transport_read', 'transport_create', 'transport_update', 'transport_delete',
            'flight_read', 'flight_create', 'flight_update', 'flight_delete',
            'accounting_read', 'accounting_create', 'accounting_update', 'accounting_delete',
            'supplier_read', 'supplier_create', 'supplier_update', 'supplier_delete',
            'task_read', 'task_create', 'task_update', 'task_delete',
            'support_read', 'support_create', 'support_update', 'support_delete',
            'report_read',
            'cms_read', 'cms_create', 'cms_update', 'cms_delete',

            // ---- Back-office (HR + Agent finance) ----
            'hr_read', 'hr_create', 'hr_update', 'hr_delete',
            'agent_finance_read', 'agent_finance_create', 'agent_finance_update', 'agent_finance_delete',

            // ---- New modules ----
            'coupon_read', 'coupon_create', 'coupon_update', 'coupon_delete',
            'campaign_read', 'campaign_create', 'campaign_update', 'campaign_delete',
            'branch_read', 'branch_create', 'branch_update', 'branch_delete',
            'insurance_read', 'insurance_create', 'insurance_update', 'insurance_delete',
            'student_service_read', 'student_service_create', 'student_service_update', 'student_service_delete',
            'medical_tour_read', 'medical_tour_create', 'medical_tour_update', 'medical_tour_delete',
            'corporate_travel_read', 'corporate_travel_create', 'corporate_travel_update', 'corporate_travel_delete',
            'event_tour_read', 'event_tour_create', 'event_tour_update', 'event_tour_delete',
            'review_read', 'review_update', 'review_delete',

            // ---- Portals (preview) ----
            'customer_portal_read',
            'agent_portal_read',
            'staff_portal_read',
            // Super Admin owns every installed module, including SaaS.
            'saas_read',
        ]);
    }

    /**
     * SaaS Super Admin — the PLATFORM owner. Sees ONLY the separate SaaS panel
     * (tenants, plans, subscriptions, domains). No company ERP access at all.
     */
    private function saasSuperAdminPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'saas_read',
        ]);
    }

    /**
     * Admin — runs the agency day to day. Everything except SaaS control and
     * the customer/agent/staff portal preview menus.
     */
    private function adminPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'dashboard_read',

            'user_read', 'user_create', 'user_update', 'user_delete', 'permission_update',
            'role_read', 'role_create', 'role_update', 'role_delete',

            'language_read', 'language_create', 'language_update', 'language_delete', 'language_phrase_update',

            'general_settings_read', 'general_settings_update',
            'mail_settings_read', 'mail_settings_update',
            'sms_settings_read', 'sms_settings_update',
            'payment_settings_read', 'payment_settings_update',
            'storage_settings_read', 'storage_settings_update',
            'recaptcha_settings_read', 'recaptcha_settings_update',

            'todo_read', 'todo_create', 'todo_update', 'todo_delete',

            'activity_logs_read', 'activity_logs_view', 'login_activity_read',

            // ---- Travel ERP modules ----
            'package_read', 'package_create', 'package_update', 'package_delete',
            'booking_read', 'booking_create', 'booking_update', 'booking_delete',
            'customer_read', 'customer_create', 'customer_update', 'customer_delete',
            'crm_read', 'crm_create', 'crm_update', 'crm_delete',
            'visa_read', 'visa_create', 'visa_update', 'visa_delete',
            'tour_read', 'tour_create', 'tour_update', 'tour_delete',
            'hajj_read', 'hajj_create', 'hajj_update', 'hajj_delete',
            'hotel_read', 'hotel_create', 'hotel_update', 'hotel_delete',
            'transport_read', 'transport_create', 'transport_update', 'transport_delete',
            'flight_read', 'flight_create', 'flight_update', 'flight_delete',
            'accounting_read', 'accounting_create', 'accounting_update', 'accounting_delete',
            'supplier_read', 'supplier_create', 'supplier_update', 'supplier_delete',
            'task_read', 'task_create', 'task_update', 'task_delete',
            'support_read', 'support_create', 'support_update', 'support_delete',
            'report_read',
            'cms_read', 'cms_create', 'cms_update', 'cms_delete',

            // ---- Back-office (HR + Agent finance) ----
            'hr_read', 'hr_create', 'hr_update', 'hr_delete',
            'agent_finance_read', 'agent_finance_create', 'agent_finance_update', 'agent_finance_delete',

            // ---- New modules ----
            'coupon_read', 'coupon_create', 'coupon_update', 'coupon_delete',
            'campaign_read', 'campaign_create', 'campaign_update', 'campaign_delete',
            'branch_read', 'branch_create', 'branch_update', 'branch_delete',
            'insurance_read', 'insurance_create', 'insurance_update', 'insurance_delete',
            'student_service_read', 'student_service_create', 'student_service_update', 'student_service_delete',
            'medical_tour_read', 'medical_tour_create', 'medical_tour_update', 'medical_tour_delete',
            'corporate_travel_read', 'corporate_travel_create', 'corporate_travel_update', 'corporate_travel_delete',
            'event_tour_read', 'event_tour_create', 'event_tour_update', 'event_tour_delete',
            'review_read', 'review_update', 'review_delete',
        ]);
    }

    /**
     * Manager — oversees sales operations and reads reports. No system settings,
     * no user/role management, no destructive delete on records.
     */
    private function managerPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'dashboard_read',
            'todo_read', 'todo_create', 'todo_update', 'todo_delete',

            'package_read', 'package_create', 'package_update',
            'booking_read', 'booking_create', 'booking_update',
            'customer_read', 'customer_create', 'customer_update',
            'crm_read', 'crm_create', 'crm_update',
            'visa_read', 'visa_create', 'visa_update',
            'tour_read', 'tour_create', 'tour_update',
            'hotel_read', 'hotel_create', 'hotel_update',
            'transport_read', 'transport_create', 'transport_update',
            'flight_read', 'flight_create', 'flight_update',
            'supplier_read', 'supplier_create', 'supplier_update',
            'task_read', 'task_create', 'task_update',
            'support_read', 'support_create', 'support_update',
            'review_read', 'review_update', 'review_delete',
            'report_read',
        ]);
    }

    /**
     * Operations / Counter Staff — does the actual booking & customer data entry.
     * No accounting, no reports, no settings, no user management.
     */
    private function operationsPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'dashboard_read',
            'todo_read', 'todo_create', 'todo_update', 'todo_delete',

            'package_read', 'package_create', 'package_update', 'package_delete',
            'booking_read', 'booking_create', 'booking_update',
            'customer_read', 'customer_create', 'customer_update',
            'crm_read', 'crm_create', 'crm_update',
            'visa_read', 'visa_create', 'visa_update',
            'tour_read', 'tour_create', 'tour_update',
            'hotel_read', 'hotel_create', 'hotel_update',
            'transport_read', 'transport_create', 'transport_update',
            'flight_read', 'flight_create', 'flight_update',
            'task_read', 'task_create', 'task_update',
            'support_read', 'support_create', 'support_update',
            'review_read', 'review_update',
        ]);
    }

    /**
     * Accountant — finance only: accounting, invoices, suppliers, financial reports.
     */
    private function accountantPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'dashboard_read',
            'accounting_read', 'accounting_create', 'accounting_update', 'accounting_delete',
            'agent_finance_read', 'agent_finance_create', 'agent_finance_update', 'agent_finance_delete',

            // ---- New modules ----
            'coupon_read', 'coupon_create', 'coupon_update', 'coupon_delete',
            'campaign_read', 'campaign_create', 'campaign_update', 'campaign_delete',
            'branch_read', 'branch_create', 'branch_update', 'branch_delete',
            'insurance_read', 'insurance_create', 'insurance_update', 'insurance_delete',
            'student_service_read', 'student_service_create', 'student_service_update', 'student_service_delete',
            'medical_tour_read', 'medical_tour_create', 'medical_tour_update', 'medical_tour_delete',
            'corporate_travel_read', 'corporate_travel_create', 'corporate_travel_update', 'corporate_travel_delete',
            'event_tour_read', 'event_tour_create', 'event_tour_update', 'event_tour_delete',
            'supplier_read', 'supplier_create', 'supplier_update',
            'report_read',
            'booking_read',
            'customer_read',
        ]);
    }

    /**
     * Support Agent — support center plus read-only customer/booking lookup.
     */
    private function supportAgentPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'dashboard_read',
            'support_read', 'support_create', 'support_update',
            'review_read', 'review_update',
            'customer_read',
            'booking_read',
        ]);
    }

    /**
     * Agent — external travel agent. Sees only the Agent Portal.
     */
    private function agentPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'agent_portal_read',
        ]);
    }

    /**
     * Customer — end customer. Sees only the Customer Portal.
     */
    private function customerPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'customer_portal_read',
        ]);
    }

    /**
     * Staff — internal employee. Sees only the Staff Portal (own tasks,
     * attendance, leave, payslips, profile). NO dashboard_read, so they are
     * not treated as an admin user.
     */
    private function staffPermissions(): array
    {
        return array_merge($this->selfPermissions(), [
            'staff_portal_read',
        ]);
    }

    /**
     * Tour Guide — leads tours; uses the mobile app (guide role) to see their
     * assignments and mark tours started/completed. No admin panel access.
     * The app resolves the guide via tour_guides.user_id.
     */
    private function tourGuidePermissions(): array
    {
        return $this->selfPermissions();
    }
}
