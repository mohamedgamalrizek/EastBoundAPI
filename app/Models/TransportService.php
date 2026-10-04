<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A transport product advertised on the public site (airport transfer, car
 * rental, coach hire…). TransportBooking records the actual jobs.
 */
class TransportService extends Model
{
    /** Mirrors transport_bookings.type, used to read a real starting fare. */
    public const VEHICLE_TYPES = ['Airport', 'Car', 'Bus', 'Train', 'Launch'];

    protected $fillable = [
        'title', 'icon', 'description', 'price_from', 'price_unit',
        'booking_type', 'vehicle_type', 'sort_order', 'status',
    ];

    protected $casts = [
        'price_from' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }
}
