<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TourSchedule extends Model
{
    protected $fillable = [
        'package_id', 'package_title', 'start_date', 'end_date', 'seats', 'booked', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    public function package()
    {
        return $this->belongsTo(\App\Models\Package::class);
    }

    /**
     * The status the UI should show.
     *
     * `status` is only what an admin picked; it goes stale the moment the last
     * seat sells or the departure date passes. Seats and dates are facts, so
     * they win over the stored value:
     *   admin closed it      -> closed
     *   departure has passed -> closed
     *   every seat taken     -> full
     *   otherwise            -> whatever the admin set
     */
    public function getEffectiveStatusAttribute(): string
    {
        if ($this->status === 'closed') {
            return 'closed';
        }

        if ($this->start_date && $this->start_date->lt(today())) {
            return 'closed';
        }

        if ($this->seats > 0 && $this->booked >= $this->seats) {
            return 'full';
        }

        return $this->status;
    }

    /** Badge colour for the effective status. */
    public function getStatusClassAttribute(): string
    {
        return match ($this->effective_status) {
            'open' => 'success',
            'full' => 'danger',
            default => 'secondary',
        };
    }
}
