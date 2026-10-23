<?php

namespace App\Models\Dmc;

use App\Models\Campaign;

class CampaignSpend extends DmcModel
{
    protected $table = 'dmc_campaign_spends';

    protected $casts = ['spent_on' => 'date', 'amount' => 'decimal:2'];

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
