<?php

namespace App\Models\Dmc;

use App\Models\Booking;

class SalesOrder extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['quotation_id' => \App\Models\Dmc\Quotation::class];
    }

    protected $table = 'dmc_sales_orders';

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function trip()
    {
        return $this->hasOne(Trip::class);
    }
}
