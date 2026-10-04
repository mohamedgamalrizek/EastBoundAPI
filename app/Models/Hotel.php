<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'name', 'city', 'country', 'category', 'image', 'description',
        'is_featured', 'rooms_count', 'price_per_night', 'status',
    ];

    protected $casts = [
        'category'        => 'integer',
        'rooms_count'     => 'integer',
        'price_per_night' => 'decimal:2',
        'is_featured'     => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function rooms()    { return $this->hasMany(HotelRoom::class); }
    public function bookings() { return $this->hasMany(HotelBooking::class); }
}
