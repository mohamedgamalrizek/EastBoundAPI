<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;

class SaasDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds for the SaaS module.
     *
     * Plans are real product data (what tenants subscribe to) and always get
     * seeded when SaaS mode is on, as do the payment-gateway credential rows —
     * the platform's gateways are configured in the database, not in .env.
     * Tenants are sample companies for demoing the platform admin — only
     * seeded when APP_DEMO=true.
     */
    public function run(): void
    {
        $this->call(PlanSeeder::class);
        $this->call(PaymentSettingSeeder::class);

        if (config('app.demo')) {
            $this->call(TenantSeeder::class);
        }
    }
}
