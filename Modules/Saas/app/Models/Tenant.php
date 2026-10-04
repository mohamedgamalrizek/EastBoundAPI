<?php

namespace Modules\Saas\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Columns stored as real table columns instead of inside the `data` JSON.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'email',
            'plan_id',
            'status',
        ];
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'tenant_id', 'id');
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'tenant_id', 'id')->where('status', 'active')->latestOfMany();
    }
}
