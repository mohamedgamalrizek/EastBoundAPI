<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Modules\Saas\Models\Plan;
use Modules\Saas\Models\Tenant;
use Modules\Saas\Models\Subscription;
use Stancl\Tenancy\Database\Models\Domain;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $plans = Plan::pluck('id', 'name');

        $tenants = [
            ['name' => 'Skyline Travels', 'email' => 'admin@skyline.com',  'plan' => 'Enterprise', 'status' => 'active',    'domain' => 'skyline.flow.test'],
            ['name' => 'Globe Tours',     'email' => 'admin@globe.com',    'plan' => 'Growth',     'status' => 'active',    'domain' => 'globe.flow.test'],
            ['name' => 'Nomad Agency',    'email' => 'admin@nomad.com',    'plan' => 'Starter',    'status' => 'pending',   'domain' => 'nomad.flow.test'],
            ['name' => 'Old Trip Co',     'email' => 'admin@oldtrip.com',  'plan' => 'Growth',     'status' => 'suspended', 'domain' => 'oldtrip.flow.test'],
        ];

        foreach ($tenants as $i => $data) {
            $planId = $plans[$data['plan']] ?? null;

            // withoutEvents: seed demo rows only — do NOT provision real tenant
            // databases here. Actual DB provisioning happens in the create flow.
            $tenant = Tenant::withoutEvents(function () use ($data, $planId) {
                return Tenant::create([
                    'id'      => (string) Str::uuid(),
                    'name'    => $data['name'],
                    'email'   => $data['email'],
                    'plan_id' => $planId,
                    'status'  => $data['status'],
                ]);
            });

            Domain::firstOrCreate(['domain' => $data['domain']], ['tenant_id' => $tenant->id]);

            if ($planId) {
                Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id'   => $planId,
                    'status'    => $data['status'] === 'suspended' ? 'cancelled' : 'active',
                    'starts_at' => Carbon::now()->subMonths($i + 1),
                    'ends_at'   => null,
                    'amount'    => Plan::find($planId)?->price ?? 0,
                ]);
            }
        }
    }
}
