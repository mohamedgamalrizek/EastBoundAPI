<?php

use Illuminate\Support\Facades\Route;
use Modules\Installer\Http\Controllers\InstallerController;

Route::prefix('install')->name('installer.')->middleware(['XSS', 'installer.guest'])->group(function () {
    Route::get('/', [InstallerController::class, 'show'])->name('show');
    Route::post('/database/test', [InstallerController::class, 'testDatabase'])->name('database.test')->middleware('throttle:10,1');
    Route::post('/', [InstallerController::class, 'store'])->name('store')->middleware('throttle:5,1');
    Route::get('/review', [InstallerController::class, 'review'])->name('review');
    Route::post('/run', [InstallerController::class, 'run'])->name('run');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete')->withoutMiddleware('installer.guest');
});
