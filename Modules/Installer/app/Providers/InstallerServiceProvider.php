<?php

namespace Modules\Installer\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;

class InstallerServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Installer';

    protected string $nameLower = 'installer';

    protected array $providers = [RouteServiceProvider::class];

    public function register(): void
    {
        parent::register();
        $this->mergeConfigFrom(__DIR__.'/../../config/installer.php', 'installer');
    }
}
