<?php

namespace App\Actions\Fortify;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Fortify's registration hook.
 *
 * The application registers users through Auth\RegisterController, which owns
 * the POST /register route; this class exists so Fortify's own registration
 * feature resolves rather than throwing "target class does not exist".
 */
class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
        ])->validate();

        // Users need a role: without one, hasPermission() has nothing to read.
        $role = Role::where('slug', 'customer')->orWhere('name', 'Customer')->first()
            ?? Role::orderByDesc('id')->first();

        return User::create([
            'name'        => $input['name'],
            'email'       => $input['email'],
            'password'    => Hash::make($input['password']),
            'role_id'     => $role?->id,
            'permissions' => $role?->permissions ?? [],
        ]);
    }
}
