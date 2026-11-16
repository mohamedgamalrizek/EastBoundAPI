<?php

namespace App\Models\Dmc;

use App\Models\Task;

class TripAlert extends DmcModel
{
    use \App\Models\Concerns\HasAssignedRecordAccess;

    public static function accessParents(): array
    {
        return ['trip_id' => \App\Models\Dmc\Trip::class];
    }

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
