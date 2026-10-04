<?php

namespace Database\Seeders;

use App\Models\Upload;
use App\Enums\Status;
use App\Models\Backend\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Repositories\Upload\UploadInterface;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SettingSeeder extends Seeder
{
    private $uploadRepo;

    public function __construct(UploadInterface $uploadRepo)
    {
        $this->uploadRepo = $uploadRepo;
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // firstOrCreate (not create): re-running on an existing install adds any
        // newly-introduced keys without overwriting values the agency has edited.
        collect($this->settings())->each(
            fn ($setting) => Setting::firstOrCreate(['key' => $setting['key']], ['value' => $setting['value']])
        );

        // migrate:fresh reseeds new upload ids/files, but the cached settings
        // array (rememberForever) would still hold stale ids — making logo() /
        // favicon() resolve to a deleted upload and fall back to a blank image.
        // Forget the caches so the next request rebuilds them from fresh rows.
        Cache::forget(tenant_cache_prefix() . 'settings');
        Cache::forget(tenant_cache_prefix() . 'favicon');
    }

    private function settings(): array
    {
        return [

            // Percent of a booking an agent earns when no rate is set on
            // their own user record.
            ['key' => 'agent_commission_rate',  'value' => '7'],
            // Tax rates applied by Accounting > Tax Reports.
            ['key' => 'vat_rate',               'value' => '15'],
            ['key' => 'ait_rate',               'value' => '5'],

            // Customer self-service cancellation (BookingController@cancel):
            // how close to the travel date a booking may still be cancelled
            // online, and what percentage of a paid amount the agency keeps
            // when it is. See App\Services\Booking\CancellationPolicy.
            ['key' => 'booking_cancellation_window_hours',   'value' => '24'],
            ['key' => 'booking_cancellation_penalty_percent', 'value' => '10'],
            ['key' => 'name',                   'value' => 'FLOW'],
            ['key' => 'phone',                  'value' => '+880 1700-000000'],
            ['key' => 'email',                  'value' => 'hello@flow.com'],
            ['key' => 'copyright',              'value' => 'FLOW. All rights reserved.'],

            // Public website appearance — professional burgundy brand palette.
            ['key' => 'frontend_primary_color',       'value' => '#A81A20'],
            ['key' => 'frontend_secondary_color',     'value' => '#F7E7E8'],
            ['key' => 'frontend_font_family',         'value' => 'Inter'],
            ['key' => 'frontend_button_radius',       'value' => '999'],
            ['key' => 'frontend_card_radius',         'value' => '20'],
            ['key' => 'frontend_input_radius',        'value' => '8'],
            ['key' => 'frontend_input_border_width',  'value' => '1'],
            ['key' => 'frontend_input_border_style',  'value' => 'solid'],

            // ---- Public website: contact block (header, footer, contact page) ----
            ['key' => 'phone_secondary',        'value' => '+880 2 5500-0000'],
            ['key' => 'whatsapp',               'value' => '+880 1700-000000'],
            ['key' => 'address',                'value' => 'House 42, Road 11, Gulshan-1, Dhaka 1212, Bangladesh'],
            ['key' => 'address_short',          'value' => 'Gulshan, Dhaka, Bangladesh'],
            ['key' => 'site_tagline',           'value' => 'Your trusted travel partner for tour packages, visa processing, flights, hotels, and Hajj & Umrah — crafting seamless journeys across the globe.'],

            // ---- Public website: social links (blank hides the icon) ----
            ['key' => 'facebook_url',           'value' => 'https://facebook.com/'],
            ['key' => 'instagram_url',          'value' => 'https://instagram.com/'],
            ['key' => 'linkedin_url',           'value' => 'https://linkedin.com/'],
            ['key' => 'youtube_url',            'value' => ''],
            ['key' => 'twitter_url',            'value' => ''],
            ['key' => 'app_store_url',          'value' => ''],
            ['key' => 'play_store_url',         'value' => ''],

            // ---- Auth pages: demo login toggle. All auth copy lives in lang/*/auth.json ----

            ['key' => 'light_theme_logo',         'value' => 
            $this->uploadRepo->uploadSeederByPath("backend/assets/img/logo/flow-logo.png")],
            // The white version, not the same file as the light logo. Seeding
            // the dark-blue wordmark here put it on the dark admin theme and
            // the app's blue sign-in header, where it is unreadable.
            ['key' => 'dark_theme_logo',          'value' => 
            $this->uploadRepo->uploadSeederByPath("backend/assets/img/logo/flow-logo-white.png")],
            ['key' => 'favicon',                  'value' => 
            $this->uploadRepo->uploadSeederByPath("backend/assets/img/logo/flow-favicon.png")],

            // Mobile app artwork. The API falls back to the website logos when
            // these are blank, but seeding them means a fresh install already
            // shows the right mark on both app backgrounds, and the buyer can
            // swap the app's branding without touching the website's.
            //   light  -> the app's white screens: splash, onboarding, OTP
            //   dark   -> the app's blue sign-in header, so it must be white
            // Dial code used when a phone is entered without a country.
            // Also what the country picker preselects on the signup forms.
            ['key' => 'default_dial_code',      'value' => '880'],
            ['key' => 'default_country_iso',    'value' => 'BD'],

            ['key' => 'app_logo_light',           'value' => 
            $this->uploadRepo->uploadSeederByPath("backend/assets/img/logo/flow-logo.png")],
            ['key' => 'app_logo_dark',            'value' => 
            $this->uploadRepo->uploadSeederByPath("backend/assets/img/logo/flow-logo-white.png")],

            /*
            |------------------------------------------------------------------
            | Third-party credentials — PLACEHOLDERS ONLY
            |------------------------------------------------------------------
            | Everything below ships blank ("xxx") with its integration switched
            | OFF. The agency enters its own keys under Settings. Our own demo
            | server's real keys live in LocalDemoSeeder, which is never part of
            | the release package — see CODECANYON-SUBMIT-NOTES.txt. Add a new
            | integration to BOTH files: the blank here, the real value there.
            */

            ['key' => 'sendmail_path',          'value' => '/usr/sbin/sendmail -bs -i'],
            ['key' => 'mail_driver',            'value' => 'smtp'],
            ['key' => 'mail_host',              'value' => 'smtp.example.com'],
            ['key' => 'mail_port',              'value' => '587'],
            ['key' => 'mail_username',          'value' => 'xxx'],
            ['key' => 'mail_password',          'value' => 'xxx'],
            ['key' => 'mail_encryption',        'value' => 'tls'],
            ['key' => 'mail_address',           'value' => 'admin@example.com'],
            ['key' => 'mail_name',              'value' => 'Example Name'],
            ['key' => 'signature',              'value' => 'Example Signature'],

            // Gmail API driver — for hosts that block outbound SMTP ports.
            // Placeholders only; the buyer supplies their own OAuth values in
            // Settings -> Mail. The secret and refresh token are encrypted on save.
            ['key' => 'gmail_client_id',        'value' => ''],
            ['key' => 'gmail_client_secret',    'value' => ''],
            ['key' => 'gmail_refresh_token',    'value' => ''],

            // API Security — shared X-App-Key for the mobile app. Seeded with
            // the same fixed default the app ships with, so a fresh backend
            // and a freshly built app pair up with no key setup. Rotate from
            // Settings -> API Security; blank switches the check off.
            ['key' => 'app_api_key',            'value' => \App\Http\Middleware\EnsureAppKey::DEFAULT_KEY],
            ['key' => 'app_api_key_generated_at', 'value' => ''],

            ['key' => 'facebook_client_id',     'value' => 'xxx'],
            ['key' => 'facebook_client_secret', 'value' => 'xxx'],
            ['key' => 'facebook_status',        'value' => Status::INACTIVE],

            ['key' => 'google_client_id',       'value' => 'xxx'],
            ['key' => 'google_client_secret',   'value' => 'xxx'],
            ['key' => 'google_android_client_id', 'value' => ''],
            ['key' => 'google_ios_client_id',     'value' => ''],
            ['key' => 'google_status',          'value' => Status::INACTIVE],

            // Online payment gateways. Off by default: with no gateway live the
            // app and the portal keep offering the record-payment methods only,
            // which is the correct behaviour for an agency taking cash.
            ['key' => 'bkash_app_key',          'value' => 'xxx'],
            ['key' => 'bkash_app_secret',       'value' => 'xxx'],
            ['key' => 'bkash_username',         'value' => 'xxx'],
            ['key' => 'bkash_password',         'value' => 'xxx'],
            ['key' => 'bkash_sandbox',          'value' => '1'],
            ['key' => 'bkash_status',           'value' => Status::INACTIVE],

            ['key' => 'sslcommerz_store_id',     'value' => 'xxx'],
            ['key' => 'sslcommerz_store_passwd', 'value' => 'xxx'],
            ['key' => 'sslcommerz_sandbox',      'value' => '1'],
            ['key' => 'sslcommerz_status',       'value' => Status::INACTIVE],

            // MiM SMS (mimsms.com). Off by default so a fresh install never
            // fires a real (billed) SMS.
            ['key' => 'mim_sms_username',       'value' => 'xxx'],
            ['key' => 'mim_sms_api_key',        'value' => 'xxx'],
            ['key' => 'mim_sms_sender_id',      'value' => 'xxx'],
            ['key' => 'mim_sms_status',         'value' => Status::INACTIVE],

            // Growth integrations — blank until configured in General Settings.
            ['key'=>'openai_api_key','value'=>''], ['key'=>'openai_model','value'=>'gpt-4o-mini'], ['key'=>'openai_api_url','value'=>'https://api.openai.com/v1'],
            ['key'=>'amadeus_api_key','value'=>''], ['key'=>'amadeus_api_secret','value'=>''], ['key'=>'amadeus_api_url','value'=>'https://test.api.amadeus.com'],
            ['key'=>'whatsapp_access_token','value'=>''], ['key'=>'whatsapp_phone_number_id','value'=>''], ['key'=>'whatsapp_api_version','value'=>'v21.0'],
            ['key'=>'loyalty_points_per_currency','value'=>'0.01'], ['key'=>'loyalty_referral_bonus','value'=>'100'],
            // Redemption: 1 point = 1 currency unit, so the default 0.01 earn
            // rate works out at 1% back. Points may cover half a booking.
            ['key'=>'loyalty_redeem_rate','value'=>'1'], ['key'=>'loyalty_min_redeem','value'=>'100'],
            ['key'=>'loyalty_max_redeem_percent','value'=>'50'],
            // Reviews wait for a human by default.
            ['key'=>'review_auto_approve','value'=>'0'],

        ];
    }
}
