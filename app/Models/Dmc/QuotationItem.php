<?php

namespace App\Models\Dmc;

use App\Models\Supplier;

class QuotationItem extends DmcModel
{
    protected $table = 'dmc_quotation_items';

    protected $casts = ['service_date' => 'date', 'unit_cost' => 'decimal:2', 'unit_price' => 'decimal:2'];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
