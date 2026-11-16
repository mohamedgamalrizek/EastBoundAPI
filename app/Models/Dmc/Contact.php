<?php

namespace App\Models\Dmc;

use App\Models\Customer;

class Contact extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['customer_id' => \App\Models\Customer::class];
    }

    protected $table = 'dmc_contacts';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
