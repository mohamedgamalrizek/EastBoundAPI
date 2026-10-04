<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('XSS', 'guest')->group(function () {
    // Portal login (public) — Customers & Agents
    Route::get('login',                         [AuthController::class, 'loginForm'])->name('loginForm');
    Route::post('login',                        [AuthController::class, 'login'])->name('login');

    // Admin login (staff only) — separate, non-advertised entrance
    Route::get('admin/login',                   [AuthController::class, 'adminLoginForm'])->name('admin.loginForm');
    Route::post('admin/login',                  [AuthController::class, 'adminLogin'])->name('admin.login');

    // token resend 
    Route::post('token/resend',                 [AuthController::class, 'resendToken'])->name('token.resend')->withoutMiddleware('guest')->middleware('throttle:4,1');

    // registration 
    Route::get('register',                      [RegisterController::class, 'registerForm'])->name('registerForm');
    Route::post('register',                     [RegisterController::class, 'register'])->name('register');
    Route::get('register/token/verify',         [RegisterController::class, 'tokenForm'])->name('register.tokenForm');
    Route::post('register/token/verify',        [RegisterController::class, 'verifyToken'])->name('register.verifyToken')->middleware('throttle:6,1');

    // Public customer login via Google or Facebook. Admin login remains password-only.
    Route::get('auth/social/{provider}', [SocialAuthController::class, 'redirect'])->name('social.login.redirect');
    Route::get('auth/social/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.login.callback');

    // password reset — throttled: these accept a six-digit code
    Route::get('password/verify/email',         [PasswordController::class, 'passwordForgotForm'])->name('password.forgotForm');
    Route::post('password/verify/email',        [PasswordController::class, 'verifyEmail'])->name('password.verify.email')->middleware('throttle:6,1');

    Route::get('password/verify/token',         [PasswordController::class, 'tokenForm'])->name('password.tokenForm');
    Route::post('password/verify/token',        [PasswordController::class, 'verifyToken'])->name('password.verifyToken')->middleware('throttle:6,1');

    Route::get('password/reset',                [PasswordController::class, 'passwordResetForm'])->name('password.resetForm');
    Route::post('password/reset',               [PasswordController::class, 'passwordReset'])->name('password.reset')->middleware('throttle:6,1');
});

// Logout — the app has always used its own AuthController@logout, but the route
// name came from Fortify. Declared here now that Fortify's routes are off.
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
