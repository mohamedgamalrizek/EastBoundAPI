<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentCommission extends Model
{
    /** pending = earned, not yet released; paid = credited to the wallet. */
    public const STATUSES = ['pending', 'paid'];

    protected $fillable = [
        'agent_id', 'customer_id', 'booking_id',
        'source_type', 'source_id',
        'reference', 'booking_ref', 'customer_name',
        'amount', 'rate', 'status', 'earned_on', 'approved_on', 'is_auto',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'rate'        => 'decimal:2',
        'earned_on'   => 'date',
        'approved_on' => 'date',
        'is_auto'     => 'boolean',
    ];

    /** Journal entries posted for this commission. */
    public function journalEntries()
    {
        return $this->morphMany(AccountTransaction::class, 'source');
    }

    /** The wallet line this commission produced once approved. */
    public function walletEntry()
    {
        return $this->morphOne(AgentWalletTransaction::class, 'source');
    }

    /** Approved commissions are money the agent can already see. */
    public function isApproved(): bool
    {
        return $this->status === 'paid';
    }

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'agent_id');
    }

    public function customer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function booking(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\Booking::class);
    }

    /** The sale that earned this commission — tour, hotel stay, transport trip. */
    public function source(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo();
    }
}
