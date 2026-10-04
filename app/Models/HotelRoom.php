<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelRoom extends Model
{
    protected $fillable = [
        'hotel_id', 'room_type', 'capacity', 'rate_per_night',
        'total_rooms', 'available_rooms', 'status',
    ];

    protected $casts = [
        'hotel_id'        => 'integer',
        'capacity'        => 'integer',
        'rate_per_night'  => 'decimal:2',
        'total_rooms'     => 'integer',
        'available_rooms' => 'integer',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function bookings() { return $this->hasMany(HotelBooking::class); }
}
