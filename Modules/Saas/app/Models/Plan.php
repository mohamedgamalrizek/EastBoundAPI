<?php

namespace Modules\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name', 'slug', 'price', 'billing_cycle', 'max_users', 'features', 'status',
    ];

    protected $casts = [
        'features' => 'array',
        'price'    => 'decimal:2',
        'status'   => 'integer',
    ];

    public function tenants()
    {
        return $this->hasMany(Tenant::class, 'plan_id', 'id');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id', 'id');
    }
}
