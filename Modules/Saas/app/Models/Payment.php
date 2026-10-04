<?php

namespace Modules\Saas\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'gateway', 'reference', 'gateway_ref',
        'tenant_id', 'plan_id', 'subscription_id',
        'payer_name', 'payer_email',
        'amount', 'currency', 'status', 'payload', 'paid_at',
    ];

    protected $casts = [
        'payload'  => 'array',
        'amount'   => 'decimal:2',
        'paid_at'  => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /** A unique, human-traceable payment reference. */
    public static function makeReference(): string
    {
        return 'PAY-' . strtoupper(Str::random(10));
    }

    public function markSuccess(?string $gatewayRef = null, array $payload = []): void
    {
        $this->update([
            'status'      => 'success',
            'gateway_ref' => $gatewayRef ?? $this->gateway_ref,
            'payload'     => array_merge((array) $this->payload, $payload),
            'paid_at'     => now(),
        ]);
    }

    public function markFailed(array $payload = []): void
    {
        $this->update([
            'status'  => 'failed',
            'payload' => array_merge((array) $this->payload, $payload),
        ]);
    }
}
