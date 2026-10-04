<?php

namespace Modules\Saas\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Saas\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'          => 'Starter',
                'price'         => 1000,
                'billing_cycle' => 'monthly',
                'max_users'     => 5,
                'features'      => ['1 Branch', 'Up to 5 users', 'Bookings & Customers', 'Email support'],
            ],
            [
                'name'          => 'Growth',
                'price'         => 3000,
                'billing_cycle' => 'monthly',
                'max_users'     => 20,
                'features'      => ['3 Branches', 'Up to 20 users', 'All travel modules', 'Accounting', 'Priority support'],
            ],
            [
                'name'          => 'Enterprise',
                'price'         => 8000,
                'billing_cycle' => 'monthly',
                'max_users'     => null,
                'features'      => ['Unlimited branches', 'Unlimited users', 'All modules + SaaS', 'Dedicated manager', '24/7 support'],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::firstOrCreate(
                ['name' => $plan['name']],
                array_merge($plan, [
                    'slug'   => Str::slug($plan['name']),
                    'status' => 1,
                ])
            );
        }
    }
}
