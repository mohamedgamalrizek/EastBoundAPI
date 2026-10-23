<?php

namespace App\Models\Dmc;

use App\Models\Customer;
use App\Models\Lead;
use App\Models\User;

class Opportunity extends DmcModel
{
    protected $table = 'dmc_opportunities';

    protected $casts = ['expected_value' => 'decimal:2', 'expected_close' => 'date'];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }
}
