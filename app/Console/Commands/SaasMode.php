<?php

namespace App\Console\Commands;

use App\Services\SaasModeWriter;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Flip the application between SINGLE-company and SAAS (multi-tenant) mode by
 * setting `enabled` in config/saas.php, then clearing the config cache so the
 * change takes effect immediately. Lets one codebase run either way.
 *
 *   php artisan saas:mode on      → SAAS mode
 *   php artisan saas:mode off     → single-company mode
 *   php artisan saas:mode         → show current mode
 */
class SaasMode extends Command
{
    protected $signature = 'saas:mode {state? : on|off (omit to show current mode)}';

    protected $description = 'Switch between single-company and SaaS (multi-tenant) mode';

    public function handle(): int
    {
        $current = config('saas.enabled');

        $state = $this->argument('state');

        if ($state === null) {
            $this->info('SaaS mode is currently: ' . ($current ? 'ON (multi-tenant)' : 'OFF (single company)'));
            return self::SUCCESS;
        }

        $state = strtolower($state);
        if (! in_array($state, ['on', 'off', 'true', 'false', '1', '0'], true)) {
            $this->error("Invalid state '{$state}'. Use: on | off");
            return self::FAILURE;
        }

        $enabled = in_array($state, ['on', 'true', '1'], true);

        try {
            app(SaasModeWriter::class)->set($enabled);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->call('config:clear');
        $this->call('route:clear');

        $this->info('SaaS mode set to: ' . ($enabled ? 'ON (multi-tenant platform)' : 'OFF (single company)'));
        $this->line('Tip: the SaaS Super Admin (saas@bugbuild.com) lands on /saas only when this is ON.');

        return self::SUCCESS;
    }
}
