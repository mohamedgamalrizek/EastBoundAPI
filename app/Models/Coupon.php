<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A promo code. Until now this was a CRUD row nothing ever read — no booking
 * path looked one up, so `usage_limit`, `min_spend` and `expires_at` were
 * decoration. The rules below are what CouponService enforces at booking time.
 */
class Coupon extends Model
{
    public const PERCENTAGE = 'Percentage';
    public const FIXED      = 'Fixed';

    protected $fillable = [
        'code', 'type', 'value', 'min_spend', 'usage_limit', 'used_count', 'expires_at', 'description', 'status',
    ];

    protected $casts = [
        'value'       => 'decimal:2',
        'min_spend'   => 'decimal:2',
        'expires_at'  => 'date',
        'used_count'  => 'integer',
        'usage_limit' => 'integer',
    ];

    public function bookings() { return $this->hasMany(Booking::class); }

    public function scopeActive($query) { return $query->where('status', 'active'); }

    /* ---- Rules ----------------------------------------------------------- */

    public function isExpired(): bool
    {
        // expires_at is a date: a coupon lasts to the end of the day printed
        // on it, so comparing against today (not now) is the honest reading.
        return $this->expires_at !== null && $this->expires_at->lt(today());
    }

    public function isExhausted(): bool
    {
        return $this->usage_limit !== null && $this->used_count >= $this->usage_limit;
    }

    public function remainingUses(): ?int
    {
        return $this->usage_limit === null ? null : max(0, $this->usage_limit - $this->used_count);
    }

    /**
     * Why this coupon cannot be used on a subtotal, or null when it can.
     * Returned as a message because every caller — API, portal, website and
     * the admin form — wants to tell the customer the same thing.
     */
    public function rejectionReason(float $subtotal): ?string
    {
        if ($this->status !== 'active') {
            return 'This coupon is no longer active.';
        }
        if ($this->isExpired()) {
            return 'This coupon expired on ' . $this->expires_at->format('d M Y') . '.';
        }
        if ($this->isExhausted()) {
            return 'This coupon has reached its usage limit.';
        }
        if ($this->min_spend !== null && $subtotal < (float) $this->min_spend) {
            return 'This coupon needs a minimum spend of ' . currency_symbol() . number_format((float) $this->min_spend, 2) . '.';
        }

        return null;
    }

    public function isUsableOn(float $subtotal): bool
    {
        return $this->rejectionReason($subtotal) === null;
    }

    /**
     * What this coupon takes off `$subtotal`. A fixed coupon worth more than
     * the booking discounts the booking, not more — nothing here may push an
     * amount below zero, which would post a negative sale to the ledger.
     */
    public function discountOn(float $subtotal): float
    {
        if ($subtotal <= 0) {
            return 0.0;
        }

        $discount = $this->type === self::PERCENTAGE
            ? $subtotal * ((float) $this->value / 100)
            : (float) $this->value;

        return round(min($discount, $subtotal), 2);
    }

    public function summary(): string
    {
        return $this->type === self::PERCENTAGE
            ? rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '% off'
            : currency_symbol() . number_format((float) $this->value, 2) . ' off';
    }
}
