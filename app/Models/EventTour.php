<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventTour extends Model
{
    protected $fillable = [
        'title', 'type', 'location', 'event_date', 'seats', 'status',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public function bookings()
    {
        return $this->hasMany(EventBooking::class);
    }

    /** Seats sold: every booking that has not been cancelled holds its seats. */
    public function seatsBooked(): int
    {
        return (int) $this->bookings()->where('status', '!=', 'Cancelled')->sum('seats');
    }

    /** `seats` is the capacity; what is left comes from the bookings. */
    public function seatsLeft(): int
    {
        return max(0, (int) $this->seats - $this->seatsBooked());
    }

    /**
     * Derive Open/Full from the bookings. Closed is a manual decision (event
     * over, sales stopped) and is never overridden here.
     */
    public function syncAvailability(): void
    {
        if ($this->status === 'Closed') {
            return;
        }

        $expected = $this->seatsLeft() <= 0 ? 'Full' : 'Open';

        if ($this->status !== $expected) {
            $this->newQuery()->whereKey($this->id)->update(['status' => $expected]);
            $this->status = $expected;
        }
    }
}
