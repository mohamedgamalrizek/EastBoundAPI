<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An agent asking to be paid out of their wallet.
 *
 * requested -> approved -> paid (or rejected). Nothing happens to the wallet
 * or the books until 'paid': that is the moment cash leaves the agency, and
 * AgentSettlementService posts it as Dr Agent Payable / Cr Cash-or-Bank.
 */
class AgentWithdrawal extends Model
{
    public const STATUSES = ['requested', 'approved', 'paid', 'rejected'];

    /** Methods, mapped to the account the money goes out of. */
    public const METHODS = ['Bank', 'Cash', 'bKash', 'Nagad'];

    protected $fillable = [
        'agent_id', 'reference', 'amount', 'method', 'account_details',
        'status', 'requested_on', 'processed_on', 'processed_by', 'note',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'requested_on' => 'date',
        'processed_on' => 'date',
    ];

    public function agent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function processedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /** Journal entries posted for this payout. */
    public function journalEntries()
    {
        return $this->morphMany(AccountTransaction::class, 'source');
    }

    /** Still waiting on the office — the amount is reserved but not yet paid. */
    public function isOpen(): bool
    {
        return in_array($this->status, ['requested', 'approved'], true);
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['requested', 'approved']);
    }
}
