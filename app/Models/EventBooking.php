<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventBooking extends Model
{
    protected $fillable = [
        'event_tour_id', 'customer_id', 'booking_no', 'customer_name',
        'seats', 'amount', 'status', 'payment_method',
    ];

    protected $casts = [
        'event_tour_id' => 'integer',
        'customer_id'   => 'integer',
        'seats'         => 'integer',
        'amount'        => 'decimal:2',
    ];

    public function eventTour(): BelongsTo
    {
        return $this->belongsTo(EventTour::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** The invoice this booking raised, once it is confirmed. */
    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'source');
    }
}
