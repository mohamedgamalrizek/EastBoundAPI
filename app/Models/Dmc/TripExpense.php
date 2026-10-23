<?php

namespace App\Models\Dmc;

use App\Models\AccountTransaction;

class TripExpense extends DmcModel
{
    protected $table = 'dmc_trip_expenses';

    protected $casts = ['amount' => 'decimal:2', 'spent_on' => 'date'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function journalEntries()
    {
        return $this->morphMany(AccountTransaction::class, 'source');
    }
}
