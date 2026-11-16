<?php

namespace App\Models\Dmc;

use App\Models\Supplier;

class VendorOrder extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['trip_id' => \App\Models\Dmc\Trip::class];
    }

    protected $table = 'dmc_vendor_orders';

    protected $casts = ['required_on' => 'date', 'amount' => 'decimal:2'];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function rfq()
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function bills()
    {
        return $this->hasMany(VendorBill::class);
    }
}
