<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentWalletTransaction extends Model
{
    protected $fillable = [
        'agent_id',
        'reference', 'type', 'amount',
        'description', 'txn_date', 'balance_after',
        // The commission or withdrawal this line came from. Wallet lines are
        // never typed in: AgentSettlementService writes them from their source.
        'source_type', 'source_id',
    ];

    protected $casts = [
        'amount'        => 'decimal:2',
        'balance_after' => 'decimal:2',
        'txn_date'      => 'date',
    ];

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }
}
