<?php

namespace App\Http\Controllers\Backend;

use App\Enums\Status;
use App\Enums\SmsGatewayProvider;
use App\Models\Currency;
use App\Services\Messaging\SmsService;
use App\Services\Payments\PaymentManager;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Requests\MailTestRequest;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use App\Models\Backend\Setting;
use App\Models\Backend\Language;
use App\Repositories\Language\LanguageInterface;
use App\Repositories\Settings\SettingsInterface;

class SettingsController extends Controller
{
    protected $repo;
    protected $repoLang;

    public function __construct(SettingsInterface $repo, LanguageInterface $repoLang)
    {
        $this->repo         = $repo;
        $this->repoLang     = $repoLang;
    }

    public function generalSettings()
    {
        $currencies = Currency::all();
        $languages  = $this->repoLang->all(status: Status::ACTIVE);
        return view('backend.settings.general_settings.index', compact('currencies', 'languages'));
    }

    /** CMS & Website → Appearance; values still persist in the settings table. */
    public function appearance()
    {
        return view('backend.settings.appearance.index');
    }

    public function aiSettings() { return view('backend.settings.integrations.ai'); }
    public function flightApiSettings() { return view('backend.settings.integrations.flight'); }
    public function whatsappSettings() { return view('backend.settings.integrations.whatsapp'); }
    public function loyaltySettings() { return view('backend.settings.integrations.loyalty'); }

    public function updateSettings(Request $request)
    {
        // The rest of this form is saved as a blind key -> value dump (see
        // SettingsRepository::UpdateSettings), so the two booking-policy
        // numbers are validated here rather than in a FormRequest that this
        // route does not use.
        $request->validate([
            'booking_cancellation_window_hours'   => ['nullable', 'integer', 'min:0'],
            'booking_cancellation_penalty_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'frontend_primary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'frontend_secondary_color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'frontend_font_family' => ['nullable', 'in:Inter,Josefin Sans,Sora'],
            'frontend_button_radius' => ['nullable', 'integer', 'min:0', 'max:999'],
            'frontend_card_radius' => ['nullable', 'integer', 'min:0', 'max:50'],
            'frontend_input_radius' => ['nullable', 'integer', 'min:0', 'max:50'],
            'frontend_input_border_width' => ['nullable', 'integer', 'min:0', 'max:5'],
            'frontend_input_border_style' => ['nullable', 'in:solid,dashed,dotted'],
            'openai_model' => ['nullable', 'string', 'max:100'], 'openai_api_url' => ['nullable', 'url'],
            'amadeus_api_url' => ['nullable', 'url'], 'whatsapp_api_version' => ['nullable', 'string', 'max:20'],
            'loyalty_points_per_currency' => ['nullable', 'numeric', 'min:0'], 'loyalty_referral_bonus' => ['nullable', 'integer', 'min:0'],
            'loyalty_redeem_rate' => ['nullable', 'numeric', 'min:0'], 'loyalty_min_redeem' => ['nullable', 'integer', 'min:0'],
            'loyalty_max_redeem_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'review_auto_approve' => ['nullable', 'in:0,1'],
        ]);

        $result = $this->repo->UpdateSettings($request);

        if ($result['status']) {
            return back()->with('success', $result['message']);
        }
        return back()->with('danger', $result['message'])->withInput();
    }

    public function mailSettings()
    {
        return view('backend.settings.mail.index');
    }

    public function testSendMail(MailTestRequest $request)
    {
        $result = $this->repo->mailSendTest($request);

        if ($result['status']) {
            return  redirect()->route('settings.mail')->with('success', $result['message']);
        }
        return  redirect()->back()->with('danger', $result['message'])->withInput();
    }


    public function recaptcha()
    {
        return view('backend.settings.recaptcha.index');
    }

    /**
     * Settings → SMS. The balance is only pulled when the gateway is switched
     * on, so an unconfigured install never waits on a remote call.
     */
    public function sms()
    {
        $balance = SmsService::enabled()
            ? SmsService::getBalance(SmsGatewayProvider::MimSMS)
            : null;

        return view('backend.settings.sms.index', compact('balance'));
    }

    /** Settings → Push Notifications (FCM). */
    public function push()
    {
        return view('backend.settings.push.index', [
            'configured' => \App\Services\Messaging\PushService::enabled(),
        ]);
    }

    public function apiSecurity()
    {
        return view('backend.settings.api_security.index', [
            'configured' => \App\Http\Middleware\EnsureAppKey::configuredKey() !== '',
        ]);
    }

    public function socialLoginSettingsIndex()
    {
        return view('backend.settings.social_login_settings.index');
    }

    public function updateSocialLoginSettings(Request $request, string $provider)
    {
        abort_unless(in_array($provider, ['facebook', 'google'], true), 404);

        $data = $request->validate([
            "{$provider}_client_id" => ['nullable', 'string', 'max:255'],
            "{$provider}_client_secret" => ['nullable', 'string', 'max:8192'],
            // Facebook's client token is public and is required by the native
            // SDK; keep it separate from the server-side app secret.
            'facebook_client_token' => ['nullable', 'string', 'max:255'],
            // Mobile OAuth clients (Google only): the Android/iOS client ids
            // the app's native sign-in uses. Kept separate from the web
            // client; the API accepts an ID token addressed to any of them.
            'google_android_client_id' => ['nullable', 'string', 'max:255'],
            'google_ios_client_id' => ['nullable', 'string', 'max:255'],
        ]);

        foreach (["{$provider}_client_id", "{$provider}_client_secret"] as $key) {
            $value = $data[$key] ?? null;
            if ($key === "{$provider}_client_secret") {
                if (blank($value)) {
                    continue;
                }
                $value = encrypt($value);
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Setting::updateOrCreate(
            ['key' => "{$provider}_status"],
            ['value' => $request->boolean("{$provider}_status") ? Status::ACTIVE->value : Status::INACTIVE->value]
        );

        if ($provider === 'google') {
            foreach (['google_android_client_id', 'google_ios_client_id'] as $key) {
                Setting::updateOrCreate(['key' => $key], ['value' => $data[$key] ?? null]);
            }
        }

        if ($provider === 'facebook') {
            Setting::updateOrCreate(
                ['key' => 'facebook_client_token'],
                ['value' => $data['facebook_client_token'] ?? null]
            );
        }

        Cache::forget(tenant_cache_prefix() . 'settings');

        return redirect()->route('settings.social.login.index')->with('success', ucfirst($provider) . ' social login settings updated.');
    }

    /**
     * Settings → Payment Gateways. `status` tells the page which gateways are
     * genuinely live (switched on AND fully filled in), so a half-configured
     * one cannot look ready.
     */
    public function paymentGateways()
    {
        $manager = app(PaymentManager::class);

        $status = [];
        foreach ($manager->all() as $key => $gateway) {
            $status[$key] = $gateway->isEnabled();
        }

        return view('backend.settings.payment_gateways.index', compact('status'));
    }

    /** Fire one real SMS so the agency can prove the credentials work. */
    public function testSendSms(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ]);

        // ___() ignores its $replace argument, so the placeholder is swapped here.
        $message = str_replace(':app', settings('app_name') ?: config('app.name'), ___('sms.test_message'));

        $result = SmsService::send($request->phone, $message);

        if ($result['success']) {
            return redirect()->route('settings.sms.index')->with('success', ___('alert.sms_successfully_sended'));
        }

        return redirect()->back()->with('danger', $result['message'] ?: ___('alert.sms_not_send'))->withInput();
    }

    // Database Backup
    public function setLocalization($language)
    {
        $language = Language::where('code', $language)
            ->where('status', Status::ACTIVE)
            ->firstOrFail();

        App::setLocale($language->code);
        Session::put('locale', $language->code);
        return redirect()->back();
    }
}
