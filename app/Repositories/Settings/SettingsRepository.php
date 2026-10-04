<?php

namespace App\Repositories\Settings;

use App\Services\Mail\MailConfig;

use App\Models\Currency;
use App\Mail\SendTestMail;
use App\Models\Backend\Setting;
use App\Traits\ReturnFormatTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Upload\UploadInterface;

class SettingsRepository implements SettingsInterface
{
    /**
     * Settings stored encrypted at rest. A credential in a shared-hosting
     * database is one stray phpMyAdmin export away from being public, so the
     * mail password and the Gmail OAuth secrets are never written in clear.
     */
    private const ENCRYPTED_KEYS = [
        'mail_password',
        'gmail_client_secret',
        'gmail_refresh_token',
        'openai_api_key', 'amadeus_api_key', 'amadeus_api_secret', 'whatsapp_access_token',
        'facebook_client_secret',
        'google_client_secret',
    ];

    use ReturnFormatTrait;

    private $model, $upload;

    public function __construct(Setting $model, UploadInterface $upload)
    {
        $this->model = $model;
        $this->upload = $upload;
    }


    // UpdateGeneralSettings
    public function UpdateSettings($request)
    {
        try {

            if ($request->has('currency_code')) {
                $currency   = Currency::where('code', $request->currency_code)->first();
                $request->merge(['currency_symbol' => $currency->symbol]);
            }

            DB::beginTransaction();


            $ignore    = [];
            $ignore[] = '_token';
            $ignore[] = '_method';

            $images_keys = ['favicon', 'dark_theme_logo', 'light_theme_logo', 'app_logo_light', 'app_logo_dark', 'og_image'];

            foreach ($request->except($ignore) as $key => $value) {
                $settings       = Setting::where('key', $key)->first();

                if (!$settings) {
                    $settings       = new Setting();
                    $settings->key  = $key;
                }

                // Password fields are intentionally blank on the settings
                // page. A blank save must retain the already encrypted secret.
                if (in_array($key, self::ENCRYPTED_KEYS, true) && $value === '' && $settings->exists) {
                    continue;
                }

                if (in_array($key, $images_keys)) {
                    $settings->value    = $this->upload->uploadImage($value, 'settings/', [], $settings->value);
                } elseif (in_array($key, self::ENCRYPTED_KEYS, true)) {
                    // Read these back with decrypt_setting(), never settings().
                    $settings->value   = encrypt($value);
                } elseif ($key === \App\Http\Middleware\EnsureAppKey::SETTING_KEY) {
                    // Stamp the rotation date so the panel can show when the
                    // key last changed — useful when several people share an
                    // install and the app suddenly starts returning 401.
                    if ((string) $settings->value !== (string) $value) {
                        \App\Models\Backend\Setting::updateOrCreate(
                            ['key' => \App\Http\Middleware\EnsureAppKey::SETTING_GENERATED_AT],
                            ['value' => now()->toDateTimeString()]
                        );
                    }

                    $settings->value   = $value;
                } else {
                    $settings->value   = $value;
                }

                $settings->save();
            }

            DB::commit();

            // Tenant-aware: clears the current context's keys (central or the
            // active tenant) so logo/favicon/settings refresh immediately.
            Cache::forget(tenant_cache_prefix() . 'settings');
            Cache::forget(tenant_cache_prefix() . 'favicon');

            return $this->responseWithSuccess(___('alert.successfully_updated'));
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->responseWithError(___('alert.something_went_wrong'));
        }
    }

    public function mailSendTest($request)
    {
        try {

            // Settings -> Mail, applied in one place for every driver
            // (SMTP, Sendmail, Gmail API). See App\Services\Mail\MailConfig.
            MailConfig::apply();

            Mail::to($request->email)->send(new SendTestMail);

            return $this->responseWithSuccess(___('alert.mail_successfully_sended'), []);
        } catch (\Throwable $th) {
            return $this->responseWithError(___('alert.mail_not_send'), []);
        }
    }
}
