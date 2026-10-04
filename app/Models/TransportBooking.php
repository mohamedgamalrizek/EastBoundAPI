<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransportBooking extends Model
{
    protected $fillable = [
        'customer_id', 'agent_id', 'booking_id', 'driver_id',
        'booking_no', 'type', 'direction', 'customer_name', 'route',
        'travel_date', 'vehicle', 'fare', 'status', 'payment_method', 'payment_claimed_at',
    ];

    protected $casts = [
        'travel_date'         => 'date',
        'fare'                => 'decimal:2',
        'payment_claimed_at'  => 'datetime',
        'customer_id' => 'integer',
        'agent_id'    => 'integer',
       
      
    ];

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    /** The agent who sold the trip, when the desk attributed it. */
    public function agent()
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }

    public function booking()
    {
        return $this->belongsTo(\App\Models\Booking::class);
    }

    public function driver()
    {
        return $this->belongsTo(\App\Models\Driver::class);
    }

    /** The invoice this trip raised, once it is confirmed. */
    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'source');
    }
}
