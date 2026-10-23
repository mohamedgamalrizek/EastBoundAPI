<?php

namespace App\Models\Dmc;

class VendorPayment extends DmcModel
{
    protected $table = 'dmc_vendor_payments';

    protected $casts = ['paid_on' => 'date', 'amount' => 'decimal:2'];

    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class);
    }
}
