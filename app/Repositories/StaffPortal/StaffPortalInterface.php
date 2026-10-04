<?php

namespace App\Repositories\StaffPortal;

interface StaffPortalInterface
{
    public function dashboard();

    public function tasks();

    public function attendance();

    public function leave();

    public function storeLeave($request);

    public function payslips();

    public function profile();

    public function updateProfile($request);
}
