<?php

namespace App\Models\Dmc;

use App\Models\Supplier;

class VendorBill extends DmcModel
{
    protected $table = 'dmc_vendor_bills';

    protected $casts = ['issued_on' => 'date', 'due_on' => 'date', 'amount' => 'decimal:2'];

    public function vendorOrder()
    {
        return $this->belongsTo(VendorOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function payments()
    {
        return $this->hasMany(VendorPayment::class);
    }
}
