<?php

namespace Modules\Saas\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id', 'status', 'starts_at', 'ends_at', 'amount',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at'   => 'date',
        'amount'    => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class, 'plan_id', 'id');
    }

    /* ---- Lifecycle helpers ---- */

    /** Grace window (days) after ends_at before the tenant is suspended. */
    public const GRACE_DAYS = 3;

    public function isLifetime(): bool
    {
        return $this->ends_at === null && in_array($this->status, ['active', 'trial']);
    }

    public function isExpired(): bool
    {
        if ($this->isLifetime()) {
            return false;
        }
        return $this->ends_at !== null && $this->ends_at->isPast();
    }

    /** Past ends_at but still inside the grace window. */
    public function inGrace(): bool
    {
        if ($this->ends_at === null) {
            return false;
        }
        return $this->ends_at->isPast()
            && now()->lessThanOrEqualTo($this->ends_at->copy()->addDays(self::GRACE_DAYS));
    }

    public function daysRemaining(): ?int
    {
        if ($this->ends_at === null) {
            return null; // lifetime
        }
        return (int) now()->startOfDay()->diffInDays($this->ends_at->copy()->startOfDay(), false);
    }
}
