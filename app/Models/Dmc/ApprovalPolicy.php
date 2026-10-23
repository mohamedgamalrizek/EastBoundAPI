<?php

namespace App\Models\Dmc;

class ApprovalPolicy extends DmcModel
{
    protected $table = 'dmc_approval_policies';

    protected $casts = ['minimum_margin' => 'decimal:2', 'maximum_markup' => 'decimal:2', 'team_leader_limit' => 'decimal:2'];
}
