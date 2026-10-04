<?php

namespace App\Repositories\Settings;

interface SettingsInterface
{

    public function UpdateSettings($request);
    public function mailSendTest($request);
}
