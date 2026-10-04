<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Booking extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->useLogName('Booking')->logOnly(['customer_name', 'amount', 'status'])->setDescriptionForEvent(fn (string $event) => $event);
    }

    protected $fillable = [
        'customer_id',
        'agent_id',
        'package_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'travel_date',
        'travelers',
        'amount',
        // What the booking would have cost before a coupon or loyalty points
        // came off it — `amount` stays the net figure the customer owes, so
        // billing and the ledger need no knowledge of discounts.
        'gross_amount',
        'coupon_id',
        'coupon_code',
        'coupon_discount',
        'points_redeemed',
        'points_discount',
        // Kept out of a paid amount when the customer cancels themselves —
        // see App\Services\Booking\CancellationPolicy.
        'cancellation_fee',
        'status',
        // How the customer paid. Used when the booking is marked paid to
        // record the receipt against the right cash/bank/wallet account.
        'payment_method',
        'notes',
        'payment_claimed_at',
    ];

    protected $casts = [
        'travel_date'         => 'date',
        'payment_claimed_at'  => 'datetime',
        'points_redeemed'     => 'integer',
    ];

    /**
     * The agent who made the sale (a User with the Agent role, nullable for
     * bookings the office took directly). This is what the agent portal scopes
     * every one of its pages by.
     */
    public function agent()
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }

    /** The invoice raised for this booking, if it has been billed. */
    public function invoice()
    {
        return $this->hasOne(\App\Models\Invoice::class);
    }

    public function refunds()
    {
        return $this->hasMany(\App\Models\Refund::class);
    }

    // A booking belongs to one customer (nullable).
    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    // A booking belongs to one package (nullable).
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function flightBookings()    { return $this->hasMany(FlightBooking::class); }
    public function transportBookings() { return $this->hasMany(TransportBooking::class); }
    public function invoices()          { return $this->hasMany(Invoice::class); }
    public function agentCommissions()  { return $this->hasMany(AgentCommission::class); }

    /** The promo code used, when one was. */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /** The customer's verdict on this trip, once they have written one. */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /** Coupon plus points — what came off the list price altogether. */
    public function totalDiscount(): float
    {
        return round((float) $this->coupon_discount + (float) $this->points_discount, 2);
    }

    public function hasDiscount(): bool
    {
        return $this->totalDiscount() > 0.009;
    }

    /**
     * The list price. Falls back to `amount` for bookings raised before
     * discounts existed, whose gross was backfilled but which a partial
     * restore or an import could still leave at zero.
     */
    public function grossAmount(): float
    {
        return (float) $this->gross_amount ?: (float) $this->amount;
    }

    // Coloured status badge matching the theme.
    public function statusBadge(): string
    {
        $map = [
            'pending'   => 'warning',
            'confirmed' => 'info',
            'paid'      => 'success',
            'cancelled' => 'danger',
        ];
        $class = $map[$this->status] ?? 'warning';
        return "<span class='bullet-badge bullet-badge-{$class}'>" . ucfirst($this->status) . "</span>";
    }
}
