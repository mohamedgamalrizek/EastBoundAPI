<?php

namespace App\Models\Dmc;

use App\Models\Task;

class TripAlert extends DmcModel
{
    protected $table = 'dmc_trip_alerts';

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
