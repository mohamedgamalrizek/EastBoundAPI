<?php

use Illuminate\Support\Facades\Route;
use Modules\Saas\Http\Controllers\SaasController;
use Modules\Saas\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| SaaS Module Web Routes
|--------------------------------------------------------------------------
| Super-admin (central) panel for managing tenants, plans & subscriptions.
| Guarded by auth + the saas_read permission. List/view only for now.
|
| Only registered when SaaS mode is ON (config/saas.php → enabled). In
| single mode these routes do not exist at all, so /saas/* returns 404.
*/

if (! config('saas.enabled')) {
    return;
}

// Public marketing landing page (guest). The SaaS product front door:
// hero, features, live pricing, FAQ — CTAs flow into tenant-signup.
Route::get('saas',    [SaasController::class, 'landing'])->name('saas.landing');
Route::get('pricing', fn () => redirect()->route('saas.landing'))->name('saas.pricing');

// Public self-serve tenant signup (guest, central domain). Picks a plan,
// creates + provisions a new tenant workspace on the spot.
Route::get('tenant-signup',  [SaasController::class, 'signup'])->name('saas.signup');
Route::post('tenant-signup', [SaasController::class, 'signupStore'])->name('saas.signup.store');

// Public payment flow (guest): checkout → pay → provider → callback → result.
Route::get('saas/checkout/{plan}',          [PaymentController::class, 'checkout'])->name('saas.checkout');
Route::post('saas/pay',                      [PaymentController::class, 'pay'])->name('saas.payment.pay');
Route::match(['get', 'post'], 'saas/payment/callback/{gateway}', [PaymentController::class, 'callback'])->name('saas.payment.callback');
Route::get('saas/payment/result/{reference}', [PaymentController::class, 'result'])->name('saas.payment.result');

Route::middleware(['auth', 'hasPermission:saas_read'])->prefix('saas')->name('saas.')->group(function () {
    Route::get('dashboard',          [SaasController::class, 'dashboard'])->name('dashboard');

    // ---- Plans CRUD ----
    Route::get('plans',                  [SaasController::class, 'plans'])->name('plans');
    Route::get('plans/create',           [SaasController::class, 'planCreate'])->name('plan.create');
    Route::post('plans/store',           [SaasController::class, 'planStore'])->name('plan.store');
    Route::get('plans/{id}/edit',        [SaasController::class, 'planEdit'])->name('plan.edit');
    Route::put('plans/update',           [SaasController::class, 'planUpdate'])->name('plan.update');
    Route::delete('plans/{id}/delete',   [SaasController::class, 'planDelete'])->name('plan.delete');

    // ---- Tenants CRUD ----
    Route::get('tenants',                [SaasController::class, 'tenants'])->name('tenants');
    Route::get('tenants/create',         [SaasController::class, 'tenantCreate'])->name('tenant.create');
    Route::post('tenants/store',         [SaasController::class, 'tenantStore'])->name('tenant.store');
    Route::get('tenants/{id}/edit',      [SaasController::class, 'tenantEdit'])->name('tenant.edit');
    Route::put('tenants/update',         [SaasController::class, 'tenantUpdate'])->name('tenant.update');
    Route::put('tenants/{id}/toggle',    [SaasController::class, 'tenantToggle'])->name('tenant.toggle');
    Route::put('tenants/{id}/provision', [SaasController::class, 'tenantProvision'])->name('tenant.provision');
    Route::delete('tenants/{id}/delete', [SaasController::class, 'tenantDelete'])->name('tenant.delete');

    // ---- Subscriptions CRUD ----
    Route::get('subscriptions',                [SaasController::class, 'subscriptions'])->name('subscriptions');
    Route::get('subscriptions/create',         [SaasController::class, 'subscriptionCreate'])->name('subscription.create');
    Route::post('subscriptions/store',         [SaasController::class, 'subscriptionStore'])->name('subscription.store');
    Route::get('subscriptions/{id}/edit',      [SaasController::class, 'subscriptionEdit'])->name('subscription.edit');
    Route::put('subscriptions/update',         [SaasController::class, 'subscriptionUpdate'])->name('subscription.update');
    Route::delete('subscriptions/{id}/delete', [SaasController::class, 'subscriptionDelete'])->name('subscription.delete');
    Route::post('subscriptions/{id}/renew',      [SaasController::class, 'subscriptionRenew'])->name('subscription.renew');
    Route::put('subscriptions/{id}/change-plan', [SaasController::class, 'subscriptionChangePlan'])->name('subscription.changePlan');

    // ---- Domains CRUD ----
    Route::get('domains',                [SaasController::class, 'domains'])->name('domains');
    Route::get('domains/create',         [SaasController::class, 'domainCreate'])->name('domain.create');
    Route::post('domains/store',         [SaasController::class, 'domainStore'])->name('domain.store');
    Route::delete('domains/{id}/delete', [SaasController::class, 'domainDelete'])->name('domain.delete');

    // ---- Payment gateways ----
    Route::get('gateways',           [PaymentController::class, 'gateways'])->name('gateways');
    Route::get('payment-records',    [PaymentController::class, 'records'])->name('payment.records');

    // ---- Platform pages (data-driven) ----
    Route::get('invoices',           [SaasController::class, 'invoices'])->name('invoices');
    Route::get('payments',           [SaasController::class, 'payments'])->name('payments');
    Route::get('feature-management', [SaasController::class, 'features'])->name('features');
    Route::get('system-settings',    [SaasController::class, 'settings'])->name('settings');
    Route::get('audit-logs',         [SaasController::class, 'audit'])->name('audit');
    Route::get('support',            [SaasController::class, 'support'])->name('support');
    Route::get('analytics',          [SaasController::class, 'analytics'])->name('analytics');
});
