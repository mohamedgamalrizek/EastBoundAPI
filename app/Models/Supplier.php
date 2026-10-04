<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'name', 'type', 'contact_person', 'phone', 'email', 'balance', 'status',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function contracts()
    {
        return $this->hasMany(SupplierContract::class);
    }

    public function transactions()
    {
        return $this->hasMany(SupplierTransaction::class);
    }

    /**
     * What we owe, from the ledger itself rather than the stored column —
     * used to keep `balance` in step whenever an entry is added or removed.
     */
    public function recalculateBalance(): float
    {
        $balance = (float) $this->transactions()->sum('credit') - (float) $this->transactions()->sum('debit');

        $this->forceFill(['balance' => $balance])->save();

        return $balance;
    }
}
