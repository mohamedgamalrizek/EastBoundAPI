<?php

namespace Modules\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Guards the per-tenant app pages: requires a tenant-session login
 * (set by TenantController::login against the tenant's own users table).
 */
class TenantAuth
{
    public function handle(Request $request, Closure $next)
    {
        if (! session('tenant_user')) {
            return redirect()->route('tenant.login');
        }

        // Subscription enforcement: a suspended tenant (expired subscription past
        // grace) cannot use the workspace until the agency renews.
        $tenant = function_exists('tenant') ? tenant() : null;
        if ($tenant && ($tenant->status ?? 'active') === 'suspended') {
            session()->flush();
            return redirect()->route('tenant.login')
                ->with('error', 'Your subscription has expired. Please renew to reactivate your workspace.');
        }

        return $next($request);
    }
}
