<?php

namespace App\Services\Auth;

use App\Models\Customer;
use App\Models\TourGuide;
use App\Models\User;

/**
 * Single serializer for the mobile account payload.
 *
 * Password login, OTP login and social login all return the same
 * `{id, account_type, name, email, phone, role, profile_photo}` shape so
 * the app's role router behaves identically however the session started.
 */
class ApiAccountPresenter
{
    public static function accountInfo(Customer|User $account): array
    {
        $isCustomer = $account instanceof Customer;

        $accountType = $isCustomer ? 'customer' : self::userAccountType($account);

        return [
            'id' => $account->id,
            'account_type' => $accountType,          // customer | agent | staff | guide
            'name' => $account->name,
            'email' => $account->email,
            'phone' => $account->phone,
            'role' => $isCustomer ? null : optional($account->role)->name,
            'profile_photo' => self::profilePhotoUrl($account, $isCustomer),
        ];
    }

    /**
     * Absolute URL for the account's photo (customers keep a relative path on
     * the record; users point at an uploads row) — same rule as the profile
     * endpoint so login and /profile always agree.
     */
    private static function profilePhotoUrl(Customer|User $account, bool $isCustomer): ?string
    {
        if ($isCustomer) {
            $path = $account->avatar;
        } else {
            $upload = $account->image;
            $path = $upload ? ($upload->image_one ?: $upload->original) : null;
        }

        return filled($path) ? asset($path) : null;
    }

    /**
     * Distinguish an agent from regular staff by their role name.
     */
    private static function userAccountType(User $user): string
    {
        $role = strtolower((string) optional($user->role)->name);

        // Guide first: a linked tour_guides row (or a "guide" role) wins, and
        // "Support Agent" must not fall into the agent bucket by substring.
        if (str_contains($role, 'guide') || TourGuide::where('user_id', $user->id)->exists()) {
            return 'guide';
        }

        return str_contains($role, 'agent') ? 'agent' : 'staff';
    }
}
