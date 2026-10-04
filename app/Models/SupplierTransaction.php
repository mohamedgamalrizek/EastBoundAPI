<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One line of a supplier's account.
 *
 * Credit raises what the agency owes (a bill), debit settles or reduces it (a
 * payment, a credit note, an adjustment). `balance_after` is the running
 * balance at that point in the statement.
 *
 * Note on credit notes: a supplier credit note is money the supplier takes off
 * what the agency owes — a rate adjustment, a service not delivered — so it
 * belongs on the debit side. It used to be listed as a credit, which made a
 * credit note *increase* the bill.
 */
class SupplierTransaction extends Model
{
    /** Entry types that add to what we owe. */
    public const CREDIT_TYPES = ['Bill'];

    /** How a payment left the agency; drives which account it is posted to. */
    public const METHODS = ['Bank', 'Cash', 'bKash', 'Nagad'];

    protected $fillable = [
        'supplier_id', 'supplier_contract_id', 'txn_date', 'type', 'method',
        'reference', 'description', 'debit', 'credit', 'balance_after',
    ];

    protected $casts = [
        'txn_date'      => 'date',
        'debit'         => 'decimal:2',
        'credit'        => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function contract()
    {
        return $this->belongsTo(SupplierContract::class, 'supplier_contract_id');
    }

    /** Journal entries posted for this line. */
    public function journalEntries()
    {
        return $this->morphMany(AccountTransaction::class, 'source');
    }

    /** Money actually leaving the agency, as opposed to a paper adjustment. */
    public function isPayment(): bool
    {
        return $this->type === 'Payment';
    }

    /** A bill increases both the cost and what is owed. */
    public function isBill(): bool
    {
        return in_array($this->type, self::CREDIT_TYPES, true);
    }

    /** The amount on this line, whichever column it sits in. */
    public function value(): float
    {
        return (float) ($this->isBill() ? $this->credit : $this->debit);
    }
}
