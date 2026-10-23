<?php

namespace App\Models\Dmc;

use App\Models\Customer;

class Contact extends DmcModel
{
    protected $table = 'dmc_contacts';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
