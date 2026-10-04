<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One attempt to pay something online.
 *
 * The record is written before the payer leaves for the provider's page, so
 * whatever comes back — success, failure, or the same callback twice — can be
 * matched to what was being paid and settled exactly once.
 */
class PaymentIntent extends Model
{
    public const STATUS_PENDING   = 'pending';
    public const STATUS_PAID      = 'paid';
    public const STATUS_FAILED    = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'reference', 'gateway', 'method',
        'payable_type', 'payable_id', 'customer_id',
        'amount', 'currency', 'status', 'gateway_ref', 'failure_reason',
        'payload', 'paid_at',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /** Merge into `payload` without dropping what a previous step stored there. */
    public function rememberPayload(array $values): void
    {
        $this->update(['payload' => array_merge((array) $this->payload, $values)]);
    }
}
