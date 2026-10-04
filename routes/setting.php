<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend\SettingsController;

//end social authentication
Route::get('localization/{language}', [SettingsController::class, 'setLocalization'])->name('setLocalization');

Route::middleware(['XSS', 'auth'])->prefix('admin/settings')->group(function () {

    // General settings
    Route::get('general-settings',              [SettingsController::class, 'generalSettings'])->name('settings.general.index')->middleware('hasPermission:general_settings_read');
    Route::get('appearance',                    [SettingsController::class, 'appearance'])->name('settings.appearance.index')->middleware('hasPermission:general_settings_read');
    Route::get('ai',                            [SettingsController::class, 'aiSettings'])->name('settings.ai.index')->middleware('hasPermission:general_settings_read');
    Route::get('flight-api',                    [SettingsController::class, 'flightApiSettings'])->name('settings.flight-api.index')->middleware('hasPermission:general_settings_read');
    Route::get('whatsapp',                      [SettingsController::class, 'whatsappSettings'])->name('settings.whatsapp.index')->middleware('hasPermission:general_settings_read');
    Route::get('loyalty',                       [SettingsController::class, 'loyaltySettings'])->name('settings.loyalty.index')->middleware('hasPermission:general_settings_read');
    Route::put('update-settings',               [SettingsController::class, 'updateSettings'])->name('settings.update')->middleware(['hasPermission:general_settings_update', 'demo.readonly']);

    // Mail Setting Routes
    Route::get('mail',                          [SettingsController::class, 'mailSettings'])->name('settings.mail')->middleware('hasPermission:mail_settings_read');
    Route::post('mail/test-send-mail',          [SettingsController::class, 'testSendMail'])->name('settings.testSendMail')->middleware(['hasPermission:mail_settings_update', 'demo.readonly']);

    // Mail Setting Routes
    Route::get('recaptcha',                     [SettingsController::class, 'recaptcha'])->name('settings.recaptcha.index')->middleware('hasPermission:recaptcha_settings_read');

    // Online payment gateway settings
    Route::get('payment-gateways',              [SettingsController::class, 'paymentGateways'])->name('settings.payment.index')->middleware('hasPermission:payment_settings_read');

    // SMS gateway settings
    Route::get('sms',                           [SettingsController::class, 'sms'])->name('settings.sms.index')->middleware('hasPermission:sms_settings_read');
    Route::post('sms/test-send-sms',            [SettingsController::class, 'testSendSms'])->name('settings.testSendSms')->middleware(['hasPermission:sms_settings_update', 'demo.readonly']);

    // Push notifications (FCM) — saved through settings.update like the rest
    Route::get('push-notifications',            [SettingsController::class, 'push'])->name('settings.push.index')->middleware('hasPermission:general_settings_read');

    // API security — shared X-App-Key for the mobile API. Saved through
    // settings.update like the other settings pages.
    Route::get('api-security',                  [SettingsController::class, 'apiSecurity'])->name('settings.api.security.index')->middleware('hasPermission:general_settings_read');

    // Social login settings
    Route::get('social-login',                  [SettingsController::class, 'socialLoginSettingsIndex'])->name('settings.social.login.index')->middleware('hasPermission:general_settings_read');
    Route::put('social-login/{provider}',       [SettingsController::class, 'updateSocialLoginSettings'])->name('settings.social.login.update')->middleware(['hasPermission:general_settings_update', 'demo.readonly']);
});
