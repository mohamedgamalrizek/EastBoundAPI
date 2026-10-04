<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Server-side verification of native mobile provider credentials.
 *
 * The app signs in with the provider SDK and hands the resulting credential
 * to the API; the credential is validated directly with Google/Facebook
 * here, never trusted on the client's word. No FLOW secret is involved
 * on the device — only the provider credential, used once and discarded.
 */
class SocialTokenVerifier
{
    public function __construct(private SocialLoginService $social)
    {
    }

    /**
     * Verify a Google ID token and return the verified profile.
     *
     * Validation is delegated to Google's tokeninfo endpoint (signature,
     * expiry and issuer checked by Google); audience, verified-email and
     * subject are enforced here against our own client ids.
     *
     * @return array{id: string, name: string, email: string}
     *
     * @throws RuntimeException on any verification failure.
     */
    public function verifyGoogle(string $idToken): array
    {
        if (blank($idToken)) {
            throw new RuntimeException('Missing Google credential.');
        }

        try {
            $info = Http::withOptions(['verify' => $this->caBundle()])
                ->timeout(15)
                ->get('https://oauth2.googleapis.com/tokeninfo', ['id_token' => $idToken])
                ->throw()
                ->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException('Google verification is temporarily unavailable.', 503, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Invalid or expired Google credential.', 401, $e);
        }

        $issuer = (string) ($info['iss'] ?? '');
        if (!in_array($issuer, ['accounts.google.com', 'https://accounts.google.com'], true)) {
            throw new RuntimeException('Invalid Google credential issuer.', 401);
        }

        if (!in_array((string) ($info['aud'] ?? ''), $this->googleClientIds(), true)) {
            throw new RuntimeException('Google credential was not issued for this app.', 401);
        }

        if ((int) ($info['exp'] ?? 0) <= time()) {
            throw new RuntimeException('Expired Google credential.', 401);
        }

        $email = strtolower((string) ($info['email'] ?? ''));
        $verified = $info['email_verified'] ?? false;
        $verified = $verified === true || $verified === 'true' || $verified === '1' || $verified === 1;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !$verified) {
            throw new RuntimeException('Google did not provide a verified email address.', 401);
        }

        if (blank($info['sub'] ?? null)) {
            throw new RuntimeException('Invalid Google credential subject.', 401);
        }

        return [
            'id' => (string) $info['sub'],
            'name' => (string) ($info['name'] ?? $email),
            'email' => $email,
        ];
    }

    /**
     * Verify a Facebook user access token and return the verified profile.
     *
     * The token is checked with the app-scoped debug_token endpoint (app id,
     * validity, expiry, user id) and the profile is read from /me with the
     * same token, so a token minted for another app or user is useless here.
     *
     * @return array{id: string, name: string, email: string}
     *
     * @throws RuntimeException on any verification failure.
     */
    public function verifyFacebook(string $accessToken, ?string $expectedUserId = null): array
    {
        if (blank($accessToken)) {
            throw new RuntimeException('Missing Facebook credential.');
        }

        $appId = (string) settings('facebook_client_id');
        $appSecret = (string) decrypt_setting('facebook_client_secret');
        if (blank($appId) || blank($appSecret)) {
            throw new RuntimeException('Facebook login is not configured.', 503);
        }

        $version = config('services.facebook.graph_version', 'v23.0');

        try {
            $debug = Http::withOptions(['verify' => $this->caBundle()])
                ->timeout(15)
                ->get("https://graph.facebook.com/{$version}/debug_token", [
                    'input_token' => $accessToken,
                    'access_token' => "{$appId}|{$appSecret}",
                ])
                ->throw()
                ->json()['data'] ?? [];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException('Facebook verification is temporarily unavailable.', 503, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Invalid or expired Facebook credential.', 401, $e);
        }

        if (empty($debug['is_valid']) || (string) ($debug['app_id'] ?? '') !== $appId) {
            throw new RuntimeException('Invalid Facebook credential.', 401);
        }

        if ((int) ($debug['expires_at'] ?? 0) <= time()) {
            throw new RuntimeException('Expired Facebook credential.', 401);
        }

        $providerId = (string) ($debug['user_id'] ?? '');
        if (blank($providerId)) {
            throw new RuntimeException('Facebook did not confirm a user id.', 401);
        }

        if (filled($expectedUserId) && $expectedUserId !== $providerId) {
            throw new RuntimeException('Facebook credential does not match this user.', 401);
        }

        try {
            $profile = Http::withOptions(['verify' => $this->caBundle()])
                ->timeout(15)
                ->get("https://graph.facebook.com/{$version}/me", [
                    'fields' => 'id,name,email',
                    'access_token' => $accessToken,
                ])
                ->throw()
                ->json();
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            throw new RuntimeException('Facebook verification is temporarily unavailable.', 503, $e);
        } catch (\Throwable $e) {
            throw new RuntimeException('Invalid or expired Facebook credential.', 401, $e);
        }

        if ((string) ($profile['id'] ?? '') !== $providerId) {
            throw new RuntimeException('Facebook credential does not match this user.', 401);
        }

        $email = strtolower((string) ($profile['email'] ?? ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Facebook did not provide an email address. Make sure the email permission is enabled.', 401);
        }

        return [
            'id' => $providerId,
            'name' => (string) ($profile['name'] ?? $email),
            'email' => $email,
        ];
    }

    /**
     * Client ids a Google ID token may be addressed to: the web OAuth
     * client plus the Android/iOS clients used by the mobile app.
     */
    public function googleClientIds(): array
    {
        $this->social->assertProvider('google');

        return array_values(array_filter([
            settings('google_client_id'),
            settings('google_android_client_id'),
            settings('google_ios_client_id'),
        ]));
    }

    private function caBundle(): string|bool
    {
        $bundle = config('services.http_ca_bundle');

        return filled($bundle) && is_file($bundle) ? $bundle : true;
    }
}
