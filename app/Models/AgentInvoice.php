<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentInvoice extends Model
{
    protected $fillable = [
        'agent_id',
        'invoice_no', 'customer_name', 'amount',
        'issued_on', 'due_on', 'status', 'method', 'paid_on',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'issued_on' => 'date',
        'due_on'    => 'date',
        'paid_on'   => 'date',
    ];

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }

    /** Wallet lines key on a `reference`; an agent invoice's is its number. */
    public function getReferenceAttribute(): string
    {
        return (string) $this->invoice_no;
    }
}
