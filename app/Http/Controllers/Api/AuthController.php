<?php

namespace App\Http\Controllers\Api;

use App\Services\Contact\PhoneNumber;

use App\Services\Auth\ApiAccountPresenter;
use App\Services\Auth\OtpService;

use App\Enums\Gender;
use App\Models\User;
use App\Models\Customer;
use App\Http\Controllers\Controller;
use App\Services\Messaging\SmsService;
use App\Traits\ApiReturnFormatTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Mobile App Authentication (FLOW Customer & Agent app — single app, role-based).
 *
 * - Customers (B2C) authenticate against the `customers` table.
 * - Agents / staff (B2B) authenticate against the `users` table.
 *
 * On success the API returns a Sanctum token plus an `account_type`
 * (customer | agent | staff) so the app can open the matching experience.
 * No business logic lives in the app — only the server decides the role.
 */
class AuthController extends Controller
{
    use ApiReturnFormatTrait;

    /**
     * Customer self-registration (B2C).
     * Agents are onboarded by the agency (web), not here.
     */
    public function register(Request $request)
    {
        // Normalise first: `phone` is unique AND a login identifier, so the
        // uniqueness check has to compare the canonical form, not whatever
        // shape the user happened to type.
        $request->merge([
            'phone' => PhoneNumber::e164($request->input('phone'), $request->input('dial_code')),
        ]);

        $validator = Validator::make($request->all(), [
            'name'            => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:30', 'unique:customers,phone'],
            // Required: the verification code is emailed, so an account with no
            // address could never be verified or recovered.
            'email'           => ['required', 'email', 'max:255', 'unique:customers,email'],
            'password'        => ['required', 'confirmed', Password::min(6)],
            'gender'          => ['required', Rule::in(array_column(Gender::cases(), 'value'))],
            'date_of_birth'   => ['nullable', 'date', 'before:10 years ago', 'after:100 years ago'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $customer = Customer::create([
            'name'          => $request->name,
            'phone'         => $request->phone,
            'email'         => $request->email,
            'password'      => $request->password, // hashed via model cast
            'gender'        => $request->gender,
            'date_of_birth' => $request->date_of_birth,
            'status'        => 'active',
        ]);

        // No token yet — the phone has to be proven first. The app sends the
        // customer to the OTP screen and calls /auth/otp/verify, which is what
        // issues the Sanctum token.
        $this->issueOtp($customer);

        return $this->responseWithSuccess('Registration successful. Verify the code sent to your email.', [
            'requires_otp' => true,
            // Both identifiers: the code goes to the email, but the OTP
            // endpoints accept either, and a client should not have to guess.
            'email'        => $customer->email,
            'phone'        => $customer->phone,
            'account'      => $this->accountInfo($customer),
        ], 201);
    }

    /**
     * Issue a verification code and email it.
     *
     * Delegates to the same service the web signup and password reset use, so
     * the code length, the expiry and the email are identical everywhere. This
     * used to text the code through the SMS gateway, which meant nobody could
     * register until the buyer had bought and configured one.
     */
    private function issueOtp(Customer $customer): bool
    {
        return OtpService::issue($customer);
    }

    /**
     * Login by email OR phone + password.
     * Tries the customer table first, then the user (agent/staff) table.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login'    => ['required', 'string'], // email or phone
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $login = $request->login;

        // 1) Customer (B2C)
        $customer = Customer::whereNotNull('password')
            ->where(fn ($q) => $q->where('email', $login)
                ->orWhereIn('phone', PhoneNumber::lookupVariants($login)))
            ->first();

        if ($customer && Hash::check($request->password, $customer->password)) {
            return $this->issueToken($customer);
        }

        // 2) User (agent / staff)
        $user = User::where(fn ($q) => $q->where('email', $login)
            ->orWhereIn('phone', PhoneNumber::lookupVariants($login)))->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return $this->issueToken($user);
        }

        return $this->responseWithError('Invalid credentials.', [], 401);
    }

    /**
     * Request an OTP for phone login or password reset. The code goes out by
     * Emailed. Accepts the account's email OR phone to find them.
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $customer = Customer::when(
            $request->filled('email'),
            fn ($q) => $q->where('email', $request->email)
        )->when(
            $request->filled('phone'),
            fn ($q) => $q->whereIn('phone', PhoneNumber::lookupVariants($request->phone))
        )->first();

        if (! $customer) {
            return $this->responseWithError('No account found for this email/phone.', [], 404);
        }

        if (! $this->issueOtp($customer)) {
            return $this->responseWithError('Could not send the OTP. Please try again.', [], 503);
        }

        return $this->responseWithSuccess('OTP sent.', [
            'email' => $customer->email,
            'phone' => $customer->phone,
        ]);
    }

    /** Verify OTP and log in. Accepts the account's email OR phone. */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'otp'   => ['required', 'string'],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $customer = Customer::when(
            $request->filled('email'),
            fn ($q) => $q->where('email', $request->email)
        )->when(
            $request->filled('phone'),
            fn ($q) => $q->whereIn('phone', PhoneNumber::lookupVariants($request->phone))
        )->first();
        // One check for match AND expiry, constant-time, shared with the web.
        if (! $customer || ! OtpService::check($customer, (string) $request->otp)) {
            return $this->responseWithError('Invalid or expired code.', [], 401);
        }

        OtpService::clear($customer);

        return $this->issueToken($customer);
    }

    /**
     * Reset a forgotten customer password using the OTP sent via
     * /auth/otp/request (email OR phone). This is the mobile counterpart of
     * the web portal's password flow, which only covers ERP users.
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => ['nullable', 'email', 'max:255', 'required_without:phone'],
            'phone'    => ['nullable', 'string', 'max:30', 'required_without:email'],
            'otp'      => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);
        if ($validator->fails()) {
            return $this->responseWithError('Validation failed.', $validator->errors(), 422);
        }

        $customer = Customer::when(
            $request->filled('email'),
            fn ($q) => $q->where('email', $request->email)
        )->when(
            $request->filled('phone'),
            fn ($q) => $q->whereIn('phone', PhoneNumber::lookupVariants($request->phone))
        )->first();

        if (! $customer || ! OtpService::check($customer, (string) $request->otp)) {
            return $this->responseWithError('Invalid or expired code.', [], 401);
        }

        $customer->update([
            'otp'            => null,
            'otp_expires_at' => null,
            'password'       => $request->password, // hashed via model cast
        ]);

        return $this->responseWithSuccess('Password reset successful. Please login.');
    }

    /**
     * The currently authenticated account (customer or user).
     */
    public function me(Request $request)
    {
        return $this->responseWithSuccess('Profile fetched.', [
            'account' => $this->accountInfo($request->user()),
        ]);
    }

    /**
     * Revoke the current access token (logout this device).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->responseWithSuccess('Logged out.');
    }

    /* ------------------------------------------------------------------ */

    private function issueToken($account)
    {
        $token = $account->createToken('flow-app')->plainTextToken;

        return $this->responseWithSuccess('Login successful.', [
            'token'   => $token,
            'account' => $this->accountInfo($account),
        ]);
    }

    /**
     * Normalize either model into a consistent account payload the app reads.
     * Shared with social login — one serializer for every entrance.
     */
    private function accountInfo($account): array
    {
        return ApiAccountPresenter::accountInfo($account);
    }
}
