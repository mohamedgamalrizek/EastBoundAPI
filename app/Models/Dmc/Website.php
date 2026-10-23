<?php

namespace App\Models\Dmc;

class Website extends DmcModel
{
    protected $table = 'dmc_websites';

    protected $casts = ['active' => 'boolean'];
}
