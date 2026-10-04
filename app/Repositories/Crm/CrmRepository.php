<?php

namespace App\Repositories\Crm;

use App\Models\CrmActivity;
use App\Repositories\Crm\CrmInterface;

class CrmRepository implements CrmInterface
{
    public function followup()
    {
        return ['activities' => CrmActivity::where('type', 'followup')->orderBy('activity_date')->get()];
    }

    public function activity()
    {
        return ['activities' => CrmActivity::where('type', 'activity')->latest('activity_date')->get()];
    }

    public function notes()
    {
        return ['activities' => CrmActivity::where('type', 'note')->latest('activity_date')->get()];
    }

    public function communication()
    {
        return ['activities' => CrmActivity::where('type', 'communication')->latest('activity_date')->get()];
    }
}
