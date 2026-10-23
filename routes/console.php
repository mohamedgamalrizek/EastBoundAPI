<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Passport;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('passports:refresh-statuses', function () {
    $updated = Passport::refreshStoredStatuses();

    $this->info("Passport statuses refreshed. Updated: {$updated}");
})->purpose('Refresh stored passport statuses from expiry dates');

Artisan::command('dmc:trip-alerts', function () {
    $created = app(\App\Services\Dmc\TripOperations::class)->generateAlerts();
    $this->info('Trip readiness alerts created: '.$created);
})->purpose('Create idempotent trip readiness tasks and internal notifications');

\Illuminate\Support\Facades\Schedule::command('dmc:trip-alerts')->hourly()->withoutOverlapping();
