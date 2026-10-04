<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FlightBooking extends Model
{
    protected $fillable = [
        'customer_id', 'booking_id',
        'agent_id',
        'pnr', 'passenger_name', 'airline', 'route',
        'flight_date', 'ticket_no', 'fare', 'status',
        'refund_amount', 'penalty', 'status_note', 'status_changed_at',
    ];

    protected $casts = [
        'flight_date'       => 'date',
        'status_changed_at' => 'datetime',
        'fare'              => 'decimal:2',
        'refund_amount'     => 'decimal:2',
        'penalty'           => 'decimal:2',
    ];

    /**
     * Where a ticket may go next.
     *
     * A flown or already-settled ticket is final; without this the status
     * dropdown would happily "cancel" a ticket that was refunded last month.
     */
    public const TRANSITIONS = [
        'Pending'   => ['Confirmed', 'Cancelled'],
        'Confirmed' => ['Reissued', 'Cancelled'],
        'Reissued'  => ['Cancelled'],
        'Cancelled' => ['Refunded'],
        'Refunded'  => [],
    ];

    public function nextStatuses(): array
    {
        return self::TRANSITIONS[$this->status] ?? [];
    }

    public function canMoveTo(string $status): bool
    {
        return in_array($status, $this->nextStatuses(), true);
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'Confirmed' => 'success',
            'Reissued'  => 'info',
            'Cancelled' => 'danger',
            'Refunded'  => 'secondary',
            default     => 'warning',
        };
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function booking()
    {
        return $this->belongsTo(\App\Models\Booking::class);
    }

    /** The agent who sold this ticket, when a sale was attributed. */
    public function agent()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
