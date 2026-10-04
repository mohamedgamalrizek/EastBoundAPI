<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelBooking extends Model
{
    protected $fillable = [
        'booking_no', 'hotel_id', 'customer_id', 'agent_id', 'hotel_room_id',
        'guest_name', 'check_in', 'check_out', 'nights', 'amount', 'status',
        'payment_method', 'payment_claimed_at',
    ];

    protected $casts = [
        'hotel_id'            => 'integer',
        'customer_id'         => 'integer',
        'hotel_room_id'       => 'integer',
        'check_in'            => 'date',
        'check_out'           => 'date',
        'nights'              => 'integer',
        'amount'              => 'decimal:2',
        'payment_claimed_at'  => 'datetime',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** The agent who sold the stay, when the desk attributed it. */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function hotelRoom(): BelongsTo
    {
        return $this->belongsTo(HotelRoom::class, 'hotel_room_id');
    }

    /** The invoice this stay raised, once it is confirmed. */
    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'source');
    }
}
