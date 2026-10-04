<?php

namespace Modules\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Installer\Services\InstallationHealth;

class EnsureInstalled
{
    public function __construct(private InstallationHealth $health) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('installer.*') || $request->is('up')) {
            return $next($request);
        }

        return $this->health->installed() ? $next($request) : redirect()->route('installer.show');
    }
}
