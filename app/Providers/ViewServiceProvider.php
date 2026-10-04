<?php

namespace App\Providers;

use App\View\Composers\LangComposer;
use App\View\Composers\NavigationComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer(['backend.partials.navbar', 'frontend.partials.header'], LangComposer::class);

        // CMS-managed public navigation.
        View::composer(
            ['frontend.partials.header', 'frontend.partials.footer'],
            NavigationComposer::class
        );
    }
}
