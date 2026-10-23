<?php

namespace App\Models\Dmc;

use App\Models\Supplier;

class Rfq extends DmcModel
{
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
