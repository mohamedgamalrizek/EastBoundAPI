<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One journal entry, stored as a single row with two legs:
 * `account_id` (the account being classified) and `contra_account_id` (where
 * the money came from or went to). Which leg is debited depends on the type —
 * see DEBIT_ON_CONTRA below.
 */
class AccountTransaction extends Model
{
    /**
     * Entry types and their posting direction.
     *
     * income / receipt  -> Dr contra, Cr account
     *      money comes in: the cash/bank/receivable side is debited and the
     *      income (or receivable being settled) account is credited.
     * expense / payment / transfer -> Dr account, Cr contra
     *      money goes out: the expense (or payable/destination) account is
     *      debited and the cash/bank side is credited.
     */
    public const TYPES = ['income', 'expense', 'receipt', 'payment', 'transfer'];

    public const DEBIT_ON_CONTRA = ['income', 'receipt'];

    protected $fillable = [
        'account_id', 'contra_account_id', 'txn_date', 'account_name', 'type',
        'amount', 'reference', 'description', 'source_type', 'source_id',
    ];

    protected $casts = [
        'account_id'        => 'integer',
        'contra_account_id' => 'integer',
        'source_id'         => 'integer',
        'txn_date'          => 'date',
        'amount'            => 'decimal:2',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contraAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'contra_account_id');
    }

    /* ---------------------------------------------------------------------
     | Posting direction
     * ------------------------------------------------------------------- */

    /** Account id that is debited by this entry (null if that leg is unset). */
    public function debitAccountId(): ?int
    {
        return $this->isDebitOnContra() ? $this->contra_account_id : $this->account_id;
    }

    /** Account id that is credited by this entry (null if that leg is unset). */
    public function creditAccountId(): ?int
    {
        return $this->isDebitOnContra() ? $this->account_id : $this->contra_account_id;
    }

    /** True when this entry brings money in (debit falls on the contra side). */
    public function isDebitOnContra(): bool
    {
        return in_array($this->type, self::DEBIT_ON_CONTRA, true);
    }

    /** Entries created automatically from an invoice, receipt, payslip, … */
    public function isAutoPosted(): bool
    {
        return $this->source_type !== null;
    }

    /** Human label for where an auto-posted entry came from. */
    public function sourceLabel(): ?string
    {
        return $this->source_type ? class_basename($this->source_type) : null;
    }

    public function scopeBetween($query, $from = null, $to = null)
    {
        if ($from) {
            $query->whereDate('txn_date', '>=', $from);
        }
        if ($to) {
            $query->whereDate('txn_date', '<=', $to);
        }

        return $query;
    }

    /** Entries touching the given accounts on either leg. */
    public function scopeTouchingAccounts($query, array $accountIds)
    {
        return $query->where(fn ($q) => $q
            ->whereIn('account_id', $accountIds)
            ->orWhereIn('contra_account_id', $accountIds));
    }
}
