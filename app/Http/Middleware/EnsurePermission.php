<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission): Response
    {
        abort_unless(\App\Support\DmcModules::permissionEnabled($permission)
            && \App\Support\DmcModules::routeEnabled($request->route()?->getName()), 404);

        if (Auth::check() && Auth::user()->can_access($permission)) {
            return $next($request);
        }

        abort(403);
    }
}
