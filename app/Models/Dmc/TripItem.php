<?php

namespace App\Models\Dmc;

use App\Models\Driver;
use App\Models\Hotel;
use App\Models\Supplier;
use App\Models\TourGuide;

class TripItem extends DmcModel
{
    protected $table = 'dmc_trip_items';

    protected $casts = ['service_date' => 'date'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function quotationItem()
    {
        return $this->belongsTo(QuotationItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function guide()
    {
        return $this->belongsTo(TourGuide::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }
}
