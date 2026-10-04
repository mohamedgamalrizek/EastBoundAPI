<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * A published fare deal shown on the public flight-booking page.
 */
class FlightRoute extends Model
{
    protected $fillable = [
        'origin', 'origin_code', 'destination', 'destination_code',
        'airline', 'fare', 'trip_type', 'is_featured', 'sort_order', 'status',
    ];

    protected $casts = [
        'fare'        => 'decimal:2',
        'is_featured' => 'boolean',
        'sort_order'  => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('fare');
    }

    public function getLabelAttribute(): string
    {
        return "{$this->origin} → {$this->destination}";
    }
}
