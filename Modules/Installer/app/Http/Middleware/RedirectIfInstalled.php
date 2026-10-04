<?php

namespace Modules\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Installer\Services\InstallationHealth;

class RedirectIfInstalled
{
    public function __construct(private InstallationHealth $health) {}

    public function handle(Request $request, Closure $next)
    {
        return $this->health->installed() ? redirect()->route('admin.loginForm') : $next($request);
    }
}
