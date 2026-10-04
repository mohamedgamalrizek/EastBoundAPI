<?php

namespace Modules\Installer\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Installer\Services\EnvironmentWriter;

class EnsureApplicationKey
{
    public function __construct(private EnvironmentWriter $environment) {}

    public function handle(Request $request, Closure $next)
    {
        if (! empty(config('app.key'))) {
            return $next($request);
        }

        $key = 'base64:'.base64_encode(random_bytes(32));
        $this->environment->write(['APP_KEY' => $key]);
        config(['app.key' => $key]);

        return $next($request);
    }
}
