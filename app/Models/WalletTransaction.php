<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = [
        'customer_id', 'reference', 'type', 'amount', 'description', 'txn_date', 'balance_after',
        // The receipt or refund this line belongs to, and how the money moved.
        // A line with no source is a plain top-up or adjustment — the only
        // kind that posts to the journal on its own.
        'source_type', 'source_id', 'method',
    ];

    protected $casts = [
        'txn_date'      => 'date',
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
