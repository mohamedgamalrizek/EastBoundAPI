<?php

namespace App\Models\Dmc;

class PurchaseRequest extends DmcModel
{
    protected $table = 'dmc_purchase_requests';

    protected $casts = ['required_on' => 'date', 'estimated_cost' => 'decimal:2'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function tripItem()
    {
        return $this->belongsTo(TripItem::class);
    }

    public function rfqs()
    {
        return $this->hasMany(Rfq::class);
    }

    public function order()
    {
        return $this->hasOne(VendorOrder::class);
    }
}
