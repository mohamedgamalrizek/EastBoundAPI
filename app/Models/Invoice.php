<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    /**
     * Statuses are derived from the invoice's receipts (see
     * LedgerService::reconcileInvoice) — they are never typed by hand, so the
     * list can no longer show a "paid" invoice that nobody has paid.
     */
    public const STATUSES = ['unpaid', 'partial', 'paid', 'overdue', 'refunded'];

    protected $fillable = [
        'customer_id', 'booking_id', 'source_type', 'source_id',
        'invoice_no', 'customer_name', 'issue_date', 'due_date', 'amount',
        'paid_amount', 'refunded_amount', 'status',
    ];

    protected $casts = [
        'issue_date'  => 'date',
        'due_date'    => 'date',
        'amount'          => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'refunded_amount' => 'decimal:2',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Booking::class);
    }

    /**
     * The service document this invoice bills, when it is not a tour booking:
     * a hotel booking, transport trip, event booking or hajj pilgrim. Tour
     * bookings keep their original hard link via booking_id.
     */
    public function source(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }

    public function receipts() { return $this->hasMany(Receipt::class); }

    public function refunds() { return $this->hasMany(Refund::class); }

    /** Journal entries posted for this invoice. */
    public function journalEntries()
    {
        return $this->morphMany(AccountTransaction::class, 'source');
    }

    /** Amount still owed on this invoice. */
    public function dueAmount(): float
    {
        return round((float) $this->amount - (float) $this->paid_amount, 2);
    }
}
