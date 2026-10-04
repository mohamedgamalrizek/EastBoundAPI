<?php

namespace App\Actions\Fortify;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

/**
 * Backs Fortify's PUT /user/profile-information route.
 */
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, string>  $input
     */
    public function update($user, array $input): void
    {
        Validator::make($input, [
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'string', 'email', 'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ])->validateWithBag('updateProfileInformation');

        if ($input['email'] !== $user->email && $user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);

            return;
        }

        $user->forceFill([
            'name'  => $input['name'],
            'email' => $input['email'],
        ])->save();
    }

    /**
     * Update the given verified user's profile information; changing the email
     * invalidates the existing verification.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser($user, array $input): void
    {
        $user->forceFill([
            'name'              => $input['name'],
            'email'             => $input['email'],
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
