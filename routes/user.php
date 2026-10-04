<?php

use App\Http\Controllers\Backend\PermissionController;
use App\Http\Controllers\Backend\RoleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\UserController;

Route::middleware(['XSS', 'auth'])->group(function () {

    Route::get('admin/user/index',                      [UserController::class, 'index'])->name('user.index')->middleware('hasPermission:user_read');
    Route::get('admin/user/create',                     [UserController::class, 'create'])->name('user.create')->middleware('hasPermission:user_create');
    Route::post('admin/user/store',                     [UserController::class, 'store'])->name('user.store')->middleware('hasPermission:user_create');
    Route::get('admin/user/edit/{id}',                  [UserController::class, 'edit'])->name('user.edit')->middleware('hasPermission:user_update');
    Route::put('admin/user/update',                     [UserController::class, 'update'])->name('user.update')->middleware('hasPermission:user_update');
    Route::delete('admin/user/delete/{id}',             [UserController::class, 'delete'])->name('user.delete')->middleware('hasPermission:user_delete');

    // permissions
    Route::get('admin/user/permissions/{id}',           [UserController::class, 'permission'])->name('user.permission')->middleware('hasPermission:permission_update');
    Route::put('admin/user/permissions/update',         [UserController::class, 'permissionUpdate'])->name('user.permission.update')->middleware('hasPermission:permission_update');

    //role
    Route::get('admin/role/index',                      [RoleController::class, 'index'])->name('role.index')->middleware('hasPermission:role_read');
    Route::get('admin/role/create',                     [RoleController::class, 'create'])->name('role.create')->middleware('hasPermission:role_create');
    Route::post('admin/role/store',                     [RoleController::class, 'store'])->name('role.store')->middleware('hasPermission:role_create');
    Route::get('admin/role/edit/{id}',                  [RoleController::class, 'edit'])->name('role.edit')->middleware('hasPermission:role_update');
    Route::put('admin/role/update',                     [RoleController::class, 'update'])->name('role.update')->middleware('hasPermission:role_update');
    Route::delete('admin/role/delete/{id}',             [RoleController::class, 'delete'])->name('role.delete')->middleware('hasPermission:role_delete');
});
