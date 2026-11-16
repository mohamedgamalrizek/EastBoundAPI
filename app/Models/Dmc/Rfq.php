<?php

namespace App\Models\Dmc;

use App\Models\Supplier;

class Rfq extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['purchase_request_id' => \App\Models\Dmc\PurchaseRequest::class];
    }

    protected $table = 'dmc_rfqs';

    protected $casts = ['valid_until' => 'date', 'quoted_amount' => 'decimal:2'];

    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function order()
    {
        return $this->hasOne(VendorOrder::class);
    }
}
