<?php

namespace App\Models\Dmc;

use App\Models\User;

class Quotation extends DmcModel
{
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected $table = 'dmc_quotations';

    protected $casts = ['starts_on' => 'date', 'ends_on' => 'date', 'total_cost' => 'decimal:2', 'total_price' => 'decimal:2', 'approved_at' => 'datetime'];

    public function opportunity()
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function order()
    {
        return $this->hasOne(SalesOrder::class);
    }
}
