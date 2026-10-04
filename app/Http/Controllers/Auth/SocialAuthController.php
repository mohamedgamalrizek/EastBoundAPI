<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Repositories\LoginActivity\LoginActivityInterface;
use App\Services\Auth\SocialLoginService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SocialAuthController extends Controller
{
    public function __construct(
        private LoginActivityInterface $loginActivity,
        private SocialLoginService $social,
    ) {
    }

    public function redirect(string $provider): RedirectResponse
    {
        $this->social->assertProvider($provider);

        [$clientId, $secret] = $this->credentials($provider);
        if (blank($clientId) || blank($secret) || (int) settings("{$provider}_status") !== Status::ACTIVE->value) {
            return redirect()->route('loginForm')->with('danger', ucfirst($provider) . ' login is not configured yet.');
        }

        $state = Str::random(64);
        $payload = ['state' => $state];

        $query = [
            'client_id' => $clientId,
            'redirect_uri' => route('social.login.callback', $provider),
            'response_type' => 'code',
            'state' => $state,
        ];

        if ($provider === 'google') {
            $verifier = Str::random(96);
            $payload['code_verifier'] = $verifier;
            $query += [
                'scope' => 'openid profile email',
                'access_type' => 'online',
                'prompt' => 'select_account',
                'code_challenge' => $this->base64Url(hash('sha256', $verifier, true)),
                'code_challenge_method' => 'S256',
            ];
        } else {
            $query += [
                'display' => 'popup',
                'scope' => 'email,public_profile',
            ];
        }

        session()->put("social_oauth.{$provider}", $payload);

        $endpoint = $provider === 'google'
            ? 'https://accounts.google.com/o/oauth2/v2/auth'
            : 'https://www.facebook.com/' . config('services.facebook.graph_version', 'v23.0') . '/dialog/oauth';

        return redirect()->away($endpoint . '?' . http_build_query($query));
    }

    public function callback(string $provider): RedirectResponse
    {
        $this->social->assertProvider($provider);

        if (request()->filled('error')) {
            return redirect()->route('loginForm')->with('danger', 'Social login was cancelled.');
        }

        $oauth = session()->pull("social_oauth.{$provider}");
        if (!is_array($oauth) || !hash_equals((string) ($oauth['state'] ?? ''), (string) request('state'))) {
            return redirect()->route('loginForm')->with('danger', 'The social login session expired. Please try again.');
        }

        try {
            $profile = $this->profile($provider, (string) request('code'), $oauth['code_verifier'] ?? null);
            $user = $this->findOrCreateUser($provider, $profile);

            if ($user->status !== Status::ACTIVE) {
                return redirect()->route('loginForm')->with('danger', 'Your account is not active. Please contact support.');
            }

            auth()->login($user, true);
            session()->regenerate();
            $this->loginActivity->addLoginActivity(request()->userAgent(), 'user_logged_in_social');

            return redirect($user->home());
        } catch (\Throwable $exception) {
            Log::warning('Social login failed', [
                'provider' => $provider,
                'message' => $exception->getMessage(),
            ]);

            return redirect()->route('loginForm')->with('danger', 'We could not complete social login. Please try again.');
        }
    }

    private function profile(string $provider, string $code, ?string $codeVerifier): array
    {
        if (blank($code)) {
            throw new \RuntimeException('OAuth provider did not return an authorization code.');
        }

        [$clientId, $secret] = $this->credentials($provider);
        $redirectUri = route('social.login.callback', $provider);

        if ($provider === 'google') {
            $token = Http::withOptions(['verify' => $this->caBundle()])->asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'code' => $code,
                'client_id' => $clientId,
                'client_secret' => $secret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code_verifier' => $codeVerifier,
            ])->throw()->json();

            $profile = Http::withOptions(['verify' => $this->caBundle()])->withToken($token['access_token'] ?? '')->timeout(15)
                ->get('https://openidconnect.googleapis.com/v1/userinfo')->throw()->json();

            if (blank($profile['sub'] ?? null) || blank($profile['email'] ?? null) || !filter_var($profile['email'], FILTER_VALIDATE_EMAIL) || empty($profile['email_verified'])) {
                throw new \RuntimeException('Google did not provide a verified email address.');
            }

            return [
                'id' => (string) $profile['sub'],
                'name' => $profile['name'] ?? $profile['email'],
                'email' => strtolower($profile['email']),
            ];
        }

        $version = config('services.facebook.graph_version', 'v23.0');
        $token = Http::withOptions(['verify' => $this->caBundle()])->asForm()->timeout(15)->post("https://graph.facebook.com/{$version}/oauth/access_token", [
            'client_id' => $clientId,
            'client_secret' => $secret,
            'redirect_uri' => $redirectUri,
            'code' => $code,
        ])->throw()->json();

        $profile = Http::withOptions(['verify' => $this->caBundle()])->timeout(15)->get("https://graph.facebook.com/{$version}/me", [
            'fields' => 'id,name,email',
            'access_token' => $token['access_token'] ?? '',
        ])->throw()->json();

        if (blank($profile['id'] ?? null) || blank($profile['email'] ?? null) || !filter_var($profile['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Facebook did not provide an email address. Make sure the email permission is enabled.');
        }

        return [
            'id' => (string) $profile['id'],
            'name' => $profile['name'] ?? $profile['email'],
            'email' => strtolower($profile['email']),
        ];
    }

    private function findOrCreateUser(string $provider, array $profile): User
    {
        // Shared with mobile token exchange — identical linking rules.
        return $this->social->findOrCreateUser($provider, $profile);
    }

    private function credentials(string $provider): array
    {
        return [settings("{$provider}_client_id"), decrypt_setting("{$provider}_client_secret")];
    }

    private function base64Url(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function caBundle(): string|bool
    {
        $bundle = config('services.http_ca_bundle');

        return filled($bundle) && is_file($bundle) ? $bundle : true;
    }
}
