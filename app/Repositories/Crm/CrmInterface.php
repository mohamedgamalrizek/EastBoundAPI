<?php

namespace App\Repositories\Crm;

interface CrmInterface
{
    public function followup();

    public function activity();

    public function notes();

    public function communication();
}
