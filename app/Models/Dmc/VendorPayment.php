<?php

namespace App\Models\Dmc;

class VendorPayment extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['vendor_bill_id' => \App\Models\Dmc\VendorBill::class];
    }

    protected $table = 'dmc_vendor_payments';

    protected $casts = ['paid_on' => 'date', 'amount' => 'decimal:2'];

    public function vendorBill()
    {
        return $this->belongsTo(VendorBill::class);
    }
}
