<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Saas\Models\Tenant;
use Modules\Saas\Models\Subscription;

/**
 * Daily SaaS lifecycle: expire subscriptions whose paid period (plus grace)
 * has ended, and suspend the owning tenant so they can no longer use the app
 * until they renew.
 */
class RunSubscriptionLifecycle extends Command
{
    protected $signature = 'saas:run-subscription-lifecycle';
    protected $description = 'Expire overdue subscriptions and suspend their tenants (past grace period).';

    public function handle(): int
    {
        $grace = Subscription::GRACE_DAYS;
        $cutoff = now()->subDays($grace)->endOfDay();

        $overdue = Subscription::whereIn('status', ['active', 'trial'])
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', $cutoff)
            ->get();

        $expired = 0;
        $suspended = 0;

        foreach ($overdue as $sub) {
            $sub->update(['status' => 'expired']);
            $expired++;

            $tenant = Tenant::find($sub->tenant_id);
            if ($tenant && $tenant->status !== 'suspended') {
                $tenant->update(['status' => 'suspended']);
                $suspended++;
            }
        }

        $this->info("Subscription lifecycle: {$expired} expired, {$suspended} tenant(s) suspended (grace {$grace}d).");

        return self::SUCCESS;
    }
}
