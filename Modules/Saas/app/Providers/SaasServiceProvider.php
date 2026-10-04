<?php

namespace Modules\Saas\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Modules\Saas\Payments\PaymentManager;

class SaasServiceProvider extends ModuleServiceProvider
{
    /**
     * Register the portable payment layer: merge its config and bind the
     * gateway manager as a singleton so it travels with the module.
     */
    public function register(): void
    {
        parent::register();

        $this->mergeConfigFrom(__DIR__ . '/../../config/payment.php', 'payment');

        // Public landing/signup marketing content — drives the whole SaaS
        // front-end so it can be re-branded/re-worded without editing blades.
        $this->mergeConfigFrom(__DIR__ . '/../../config/landing.php', 'saas-landing');

        $this->app->singleton(PaymentManager::class, fn () => new PaymentManager());
    }

    /**
     * The name of the module.
     */
    protected string $name = 'Saas';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'saas';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
