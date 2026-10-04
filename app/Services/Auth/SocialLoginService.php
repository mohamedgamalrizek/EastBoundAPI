<?php

namespace App\Services\Auth;

use App\Enums\Gender;
use App\Enums\Status;
use App\Models\Customer;
use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Shared social-login account linking for web and mobile.
 *
 * Both entrances enforce identical rules: provider allow-list, enabled flag,
 * admin-account refusal, verified-email requirement (checked by the caller
 * before delegating), social-account match, safe email linking, and
 * customer-role provisioning for newcomers.
 *
 * Web (SocialAuthController) and mobile (Api\SocialAuthController) differ
 * only in how the provider profile is obtained (OAuth code exchange vs.
 * native credential verification) and how the session is issued (session
 * cookie vs. Sanctum token). Everything below that is shared.
 */
class SocialLoginService
{
    public const PROVIDERS = ['google', 'facebook'];

    public function assertProvider(string $provider): void
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);
    }

    public function isEnabled(string $provider): bool
    {
        $this->assertProvider($provider);

        return filled(settings("{$provider}_client_id"))
            && filled(decrypt_setting("{$provider}_client_secret"))
            && (int) settings("{$provider}_status") === Status::ACTIVE->value;
    }

    /**
     * Find the FLOW user for a verified provider profile, creating the
     * account on first sight.
     *
     * The profile must already be verified by the caller: id + a valid,
     * provider-verified email (lowercased here defensively).
     *
     * @param  array{id: string, name: string, email: string}  $profile
     *
     * @throws \RuntimeException  when an admin account would be linked.
     */
    public function findOrCreateUser(string $provider, array $profile): User
    {
        $this->assertProvider($provider);

        $profile['email'] = strtolower($profile['email']);

        return DB::transaction(function () use ($provider, $profile) {
            // 1) Already linked (web or mobile created it) — same user back.
            $account = SocialAccount::where('provider', $provider)
                ->where('provider_id', $profile['id'])
                ->first();
            if ($account) {
                if ($account->user?->isAdminUser()) {
                    throw new \RuntimeException('Admin accounts cannot use social login.');
                }

                return $account->user;
            }

            // 2) Existing portal login with this email — link it. Admin-type
            // accounts stay password-only.
            $user = User::whereRaw('LOWER(email) = ?', [$profile['email']])->first();
            if ($user?->isAdminUser()) {
                throw new \RuntimeException('Admin accounts cannot use social login.');
            }
            if ($user) {
                SocialAccount::create([
                    'provider' => $provider,
                    'provider_id' => $profile['id'],
                    'user_id' => $user->id,
                ]);

                return $user;
            }

            // 3) B2C customer row (app-registered) but no portal login yet —
            // attach the new login to it instead of duplicating the customer.
            $customer = Customer::whereRaw('LOWER(email) = ?', [$profile['email']])->first();
            if (!$customer) {
                $customer = Customer::create([
                    'name' => Str::limit($profile['name'], 255, ''),
                    'email' => $profile['email'],
                    'status' => 'active',
                    'notes' => 'Created from social login.',
                ]);
            }

            $role = Role::where('slug', 'customer')->firstOrFail();

            $user = new User();
            $user->name = Str::limit($profile['name'], 255, '');
            $user->email = $profile['email'];
            $user->password = Hash::make(Str::random(64));
            $user->gender = Gender::OTHERS->value;
            $user->role_id = $role->id;
            $user->permissions = $role->permissions;
            $user->customer_id = $customer->id;
            $user->status = Status::ACTIVE->value;
            $user->email_verified_at = now();
            $user->save();

            SocialAccount::create([
                'provider' => $provider,
                'provider_id' => $profile['id'],
                'user_id' => $user->id,
            ]);

            return $user;
        });
    }
}
