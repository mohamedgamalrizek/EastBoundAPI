<?php

namespace App\Http\Controllers\Api;

use App\Enums\Status;
use App\Http\Controllers\Controller;
use App\Repositories\LoginActivity\LoginActivityInterface;
use App\Services\Auth\ApiAccountPresenter;
use App\Services\Auth\SocialLoginService;
use App\Services\Auth\SocialTokenVerifier;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Mobile social sign-in (native provider SDK + token exchange).
 *
 * The app signs in with Google/Facebook natively and posts the resulting
 * credential here; the credential is verified server-side and exchanged
 * for the normal Sanctum token + account payload. No FLOW token ever
 * travels in a URL and no browser page is involved.
 */
class SocialAuthController extends Controller
{
    use ApiReturnFormatTrait;

    public function __construct(
        private SocialLoginService $social,
        private SocialTokenVerifier $verifier,
        private LoginActivityInterface $loginActivity,
    ) {
    }

    /**
     * Which providers the app may offer. Public and secret-free — the admin
     * social-login screen is the source of truth.
     */
    public function providers()
    {
        return $this->responseWithSuccess('Social providers fetched.', [
            'google' => $this->social->isEnabled('google'),
            'facebook' => $this->social->isEnabled('facebook'),
        ]);
    }

    /**
     * Exchange a Google ID token for a FLOW API session.
     */
    public function google(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_token' => ['required', 'string', 'max:8192'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        if (!$this->social->isEnabled('google')) {
            return $this->responseWithError('Google login is not enabled.', [], 403);
        }

        try {
            $profile = $this->verifier->verifyGoogle($request->input('id_token'));
        } catch (RuntimeException $e) {
            return $this->failure($e, 'google');
        }

        return $this->login('google', $profile, $request->userAgent());
    }

    /**
     * Exchange a Facebook user access token for a FLOW API session.
     */
    public function facebook(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'access_token' => ['required', 'string', 'max:8192'],
            'user_id' => ['nullable', 'string', 'max:64'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        if (!$this->social->isEnabled('facebook')) {
            return $this->responseWithError('Facebook login is not enabled.', [], 403);
        }

        try {
            $profile = $this->verifier->verifyFacebook(
                $request->input('access_token'),
                $request->input('user_id')
            );
        } catch (RuntimeException $e) {
            return $this->failure($e, 'facebook');
        }

        return $this->login('facebook', $profile, $request->userAgent());
    }

    private function login(string $provider, array $profile, ?string $userAgent)
    {
        try {
            $user = $this->social->findOrCreateUser($provider, $profile);
        } catch (RuntimeException $e) {
            return $this->responseWithError($e->getMessage(), [], 403);
        } catch (\Throwable $e) {
            Log::warning('Social login failed', ['provider' => $provider, 'message' => $e->getMessage()]);

            return $this->responseWithError('We could not complete social login. Please try again.', [], 500);
        }

        if ($user->status !== Status::ACTIVE) {
            return $this->responseWithError('Your account is not active. Please contact support.', [], 403);
        }

        $token = $user->createToken('flow-app')->plainTextToken;

        // Same audit trail as web social login. Resolved for this request
        // only (stateless API) so the activity row carries the user.
        try {
            Auth::setUser($user);
            $this->loginActivity->addLoginActivity((string) $userAgent, 'user_logged_in_social');
        } catch (\Throwable) {
            // Auditing must never break authentication.
        } finally {
            Auth::forgetGuards();
        }

        return $this->responseWithSuccess('Login successful.', [
            'token' => $token,
            'account' => ApiAccountPresenter::accountInfo($user),
        ]);
    }

    private function failure(RuntimeException $e, string $provider)
    {
        $code = (int) $e->getCode();

        if ($code === 503) {
            Log::warning('Social provider unavailable', ['provider' => $provider, 'message' => $e->getMessage()]);

            return $this->responseWithError('The social provider is temporarily unavailable. Please try again.', [], 503);
        }

        return $this->responseWithError($e->getMessage() ?: 'Invalid or expired social token.', [], 401);
    }
}
