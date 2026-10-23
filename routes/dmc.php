<?php

use App\Http\Controllers\Backend\Dmc\ManagementController;
use App\Http\Controllers\Backend\Dmc\WorkflowController;
use App\Http\Controllers\Backend\ReportController;
use Illuminate\Support\Facades\Route;

Route::middleware(['XSS', 'auth'])->prefix('dmc')->group(function () {
    Route::put('customers/{customer}/preferences', [ManagementController::class, 'customerPreferences'])->whereNumber('customer')->name('dmc.customer.preferences')->middleware('hasPermission:customer_update');
    foreach (['quotations' => 'dmc_sales_read', 'rfqs' => 'dmc_procurement_read', 'vendor-orders' => 'dmc_procurement_read'] as $module => $permission) {
        Route::get('commercial-documents/'.$module.'/{record}', [ManagementController::class, 'commercialDocument'])->whereNumber('record')->defaults('module', $module)
            ->name('dmc.document.'.$module)->middleware('hasPermission:'.$permission);
    }
    Route::get('leads/lost', [ManagementController::class, 'lostLeads'])->name('dmc.leads.lost')->middleware('hasPermission:crm_read');
    Route::get('attribution', [ManagementController::class, 'attribution'])->name('dmc.attribution')->middleware('hasPermission:crm_read');
    Route::get('trip-documents', [ManagementController::class, 'documents'])->name('dmc.documents.index')->middleware('hasPermission:dmc_operations_read');
    Route::get('trip-documents/{trip}', [ManagementController::class, 'documents'])->whereNumber('trip')->name('dmc.trip-documents')->middleware('hasPermission:dmc_operations_read');
    Route::get('alerts', [ManagementController::class, 'alerts'])->name('dmc.alerts')->middleware('hasPermission:task_read');
    Route::get('lead-performance', [ReportController::class, 'leads'])->name('dmc.lead-performance')->middleware('hasPermission:dmc_marketing_read');
    foreach (['receivables', 'profitability', 'cashflow', 'marketing', 'sales'] as $type) {
        Route::get('reports/'.$type, [ManagementController::class, 'report'])->defaults('type', $type)
            ->name('dmc.reports.'.$type)->middleware('hasPermission:'.match ($type) {
                'marketing' => 'dmc_marketing_read', 'sales' => 'dmc_sales_read', default => 'accounting_read'
            });
    }
    foreach (config('dmc-modules') as $module => $definition) {
        $read = 'hasPermission:'.$definition['permission'].'_read';
        $create = 'hasPermission:'.($definition['write_permission'] ?? $definition['permission'].'_create');
        $update = 'hasPermission:'.($definition['write_permission'] ?? $definition['permission'].'_update');
        Route::get($module, [WorkflowController::class, 'index'])->defaults('module', $module)->name('dmc.'.$module.'.index')->middleware($read);
        Route::get($module.'/create', [WorkflowController::class, 'create'])->defaults('module', $module)->name('dmc.'.$module.'.create')->middleware($create);
        Route::post($module, [WorkflowController::class, 'store'])->defaults('module', $module)->name('dmc.'.$module.'.store')->middleware($create);
        Route::get($module.'/{record}', [WorkflowController::class, 'show'])->whereNumber('record')->defaults('module', $module)->name('dmc.'.$module.'.show')->middleware($read);
        Route::get($module.'/{record}/edit', [WorkflowController::class, 'edit'])->whereNumber('record')->defaults('module', $module)->name('dmc.'.$module.'.edit')->middleware($update);
        Route::put($module.'/{record}', [WorkflowController::class, 'update'])->whereNumber('record')->defaults('module', $module)->name('dmc.'.$module.'.update')->middleware($update);
        Route::post($module.'/{record}/actions', [WorkflowController::class, 'action'])->whereNumber('record')->defaults('module', $module)->name('dmc.'.$module.'.action')->middleware($read);
    }
});
