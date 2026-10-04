<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\TenantController;
use Modules\Saas\Http\Middleware\TenantAuth;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
| The per-tenant application, served on each tenant's own domain
| (e.g. skyline.flow.test). InitializeTenancyByDomain switches the DB
| connection to that tenant's database; PreventAccessFromCentralDomains
| makes these routes a no-op on the central domain (localhost / 127.0.0.1),
| so the central `home` route is never shadowed there.
|
| Only register tenant routes when SaaS mode is on.
*/

if (! config('saas.enabled')) {
    return;
}

// Prefixed under /workspace so tenant paths NEVER collide with the central
// site's routes (/, /login, /dashboard, /customers… all exist centrally and a
// bare tenant path would overwrite them in the shared route map). The tenant
// app therefore lives at e.g. skyline.flow.test/workspace/login.
Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('workspace')->group(function () {
    Route::get('/', [TenantController::class, 'home'])->name('tenant.home');

    // Guest auth
    Route::get('login',   [TenantController::class, 'loginForm'])->name('tenant.login');
    Route::post('login',  [TenantController::class, 'login'])->name('tenant.login.attempt');
    Route::post('logout', [TenantController::class, 'logout'])->name('tenant.logout');

    // Authenticated tenant workspace
    Route::middleware(TenantAuth::class)->group(function () {
        Route::get('dashboard', [TenantController::class, 'dashboard'])->name('tenant.dashboard');
        Route::get('customers', [TenantController::class, 'customers'])->name('tenant.customers');
        Route::get('packages',  [TenantController::class, 'packages'])->name('tenant.packages');
        Route::get('bookings',  [TenantController::class, 'bookings'])->name('tenant.bookings');
    });
});
