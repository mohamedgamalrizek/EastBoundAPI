<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HajjPilgrim extends Model
{
    /**
     * How the instalment being recorded right now arrived (Cash/Bank/…).
     * A plain class property, not an attribute: it rides along from the
     * payments screen to the billing observer without touching the table.
     */
    public ?string $paymentMethodHint = null;

    protected $fillable = [
        'hajj_package_id', 'customer_id',
        'pilgrim_no', 'name', 'passport_no', 'package_title',
        'group_name', 'payment_status', 'document_status', 'status',
        'makkah_hotel', 'madinah_hotel', 'room_no',
        'flight_no', 'departure_date', 'return_date', 'seat_no',
        'amount_paid', 'amount_due',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'return_date'    => 'date',
        'amount_paid'    => 'decimal:2',
        'amount_due'     => 'decimal:2',
    ];

    /** A pilgrim needs both stays booked before the trip is hotel-ready. */
    public function hasHotels(): bool
    {
        return filled($this->makkah_hotel) && filled($this->madinah_hotel);
    }

    public function hasFlight(): bool
    {
        return filled($this->flight_no) && $this->departure_date !== null;
    }

    /**
     * Payment status follows the balance rather than being set by hand, so the
     * badge can't say "Paid" while money is still outstanding.
     */
    public function syncPaymentStatus(): void
    {
        $this->payment_status = match (true) {
            (float) $this->amount_due <= 0 && (float) $this->amount_paid > 0 => 'Paid',
            (float) $this->amount_paid > 0                                   => 'Partial',
            default                                                          => 'Pending',
        };
    }

    public function paymentTone(): string
    {
        return match ($this->payment_status) {
            'Paid'    => 'success',
            'Partial' => 'warning',
            default   => 'danger',
        };
    }

    public function hajjPackage(): BelongsTo
    {
        return $this->belongsTo(HajjPackage::class, 'hajj_package_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** The invoice mirroring this pilgrim's paid + due into the books. */
    public function invoice()
    {
        return $this->morphOne(Invoice::class, 'source');
    }
}
