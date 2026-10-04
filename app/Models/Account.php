<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    /** Account types, and which side of the ledger each one lives on. */
    public const DEBIT_TYPES  = ['Asset', 'Expense'];
    public const CREDIT_TYPES = ['Liability', 'Equity', 'Income'];

    protected $fillable = [
        'code', 'name', 'type', 'opening_balance', 'system_key', 'cash_type', 'balance',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'balance'         => 'decimal:2',
    ];

    /** Entries where this account is the primary (named) side. */
    public function transactions() { return $this->hasMany(AccountTransaction::class); }

    /** Entries where this account is the contra (paying/receiving) side. */
    public function contraTransactions() { return $this->hasMany(AccountTransaction::class, 'contra_account_id'); }

    /* ---------------------------------------------------------------------
     | Ledger helpers
     * ------------------------------------------------------------------- */

    /** True when a debit increases this account (Assets and Expenses). */
    public function isDebitNormal(): bool
    {
        return in_array($this->type, self::DEBIT_TYPES, true);
    }

    /** +1 / -1 multiplier applied to (debit - credit) to get a balance. */
    public function normalSign(): int
    {
        return $this->isDebitNormal() ? 1 : -1;
    }

    public function scopeCash($query)  { return $query->where('cash_type', 'cash'); }
    public function scopeBank($query)  { return $query->where('cash_type', 'bank'); }

    /** Per-request cache for system() lookups. */
    protected static array $systemCache = [];

    /**
     * Resolve one of the accounts the posting rules depend on, e.g.
     * Account::system('receivable'). Returns null when the chart of accounts
     * has no such account, and callers skip posting rather than guess.
     */
    public static function system(string $key): ?self
    {
        if (! array_key_exists($key, static::$systemCache)) {
            static::$systemCache[$key] = static::where('system_key', $key)->first();
        }

        return static::$systemCache[$key];
    }

    /** Drop the resolved-account cache (used after seeding/rebuilds). */
    public static function forgetSystemCache(): void
    {
        static::$systemCache = [];
    }
}
