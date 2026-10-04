<?php

namespace Modules\Installer\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InstallationHealth
{
    /**
     * Two things must both hold before the app is considered installed:
     * APP_INSTALLED must not be false, and the database must actually carry
     * the core schema. Either one failing sends the visitor to the installer.
     *
     * The flag can veto, never vouch. It used to return true on its own, so
     * APP_INSTALLED=true against an empty or dropped database walked straight
     * past the installer and failed on the first query instead.
     */
    public function installed(): bool
    {
        $configured = config('installer.installed');
        $flag = $configured === null
            ? null
            : filter_var($configured, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        // Explicitly switched off — the database is not consulted at all, so
        // a reinstall can be forced on a populated database.
        if ($flag === false) {
            return false;
        }

        if (Cache::get('installer.healthy') === true) {
            return true;
        }

        try {
            DB::connection()->getPdo();

            $tables = config('installer.required_tables', []);
            if (! collect($tables)->every(fn ($table) => Schema::hasTable($table))) {
                return false;
            }

            // Schema is present. An application_installations row proves this
            // module ran, but installs predating it have no row and are
            // perfectly healthy, so the core tables are the deciding evidence.
            Cache::put('installer.healthy', true, now()->addMinutes(10));

            return true;
        } catch (\Throwable $e) {
            // No database to check means nothing is installed yet.
            return false;
        }
    }

    public function forget(): void
    {
        Cache::forget('installer.healthy');
    }
}
