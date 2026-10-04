<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Money given back to a customer.
 *
 * A refund is its own record rather than a status on the booking: the invoice
 * stays as issued, the sale stays on the books, and the refund is posted
 * against it — Dr Refunds, Cr wherever the money went. That is what makes a
 * cancelled-and-refunded booking readable months later.
 */
class Refund extends Model
{
    /** Where the money goes back to. */
    public const METHODS = ['Cash', 'Bank', 'Wallet'];

    protected $fillable = [
        'invoice_id', 'booking_id', 'customer_id',
        'reference', 'amount', 'method', 'refunded_on', 'reason', 'processed_by',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'refunded_on' => 'date',
    ];

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function processedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** Journal entries posted for this refund. */
    public function journalEntries()
    {
        return $this->morphMany(AccountTransaction::class, 'source');
    }

    /** The wallet line, when the money went back to the customer's wallet. */
    public function walletEntry()
    {
        return $this->morphOne(WalletTransaction::class, 'source');
    }

    public function toWallet(): bool
    {
        return $this->method === 'Wallet';
    }
}
