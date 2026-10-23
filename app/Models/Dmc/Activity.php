<?php

namespace App\Models\Dmc;

use App\Models\Supplier;

class Activity extends DmcModel
{
    protected $table = 'dmc_activities';

    protected $casts = ['active' => 'boolean', 'cost' => 'decimal:2', 'price' => 'decimal:2'];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }
}
