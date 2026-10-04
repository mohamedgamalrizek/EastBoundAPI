<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    protected $fillable = [
        'invoice_id', 'customer_id',
        'receipt_no', 'customer_name', 'received_on', 'amount', 'method', 'reference',
    ];

    protected $casts = [
        'received_on' => 'date',
        'amount'      => 'decimal:2',
    ];

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
