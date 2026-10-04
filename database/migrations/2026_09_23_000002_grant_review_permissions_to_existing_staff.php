<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

/**
 * Hand the new review permissions to staff who already exist.
 *
 * `hasPermission()` reads the USER's own `permissions` column — a copy taken
 * from their role — so re-seeding the roles on an upgrade grants nobody
 * anything, and the Reviews page would 403 for every existing admin on an
 * installed site while working perfectly on a fresh one.
 *
 * This is additive: it only appends the review permissions the user's role
 * now carries, so a per-user override set in Users > Permissions survives.
 */
return new class extends Migration
{
    private const PERMISSIONS = ['review_read', 'review_update', 'review_delete'];

    public function up(): void
    {
        Role::query()->get(['id', 'permissions'])->each(function (Role $role): void {
            $granted = array_values(array_intersect($role->permissions ?? [], self::PERMISSIONS));

            if (! $granted) {
                return;
            }

            User::where('role_id', $role->id)->get(['id', 'permissions'])
                ->each(function (User $user) use ($granted): void {
                    $current = $user->permissions ?? [];
                    $missing = array_diff($granted, $current);

                    if (! $missing) {
                        return;
                    }

                    $user->permissions = array_values(array_merge($current, $missing));
                    $user->saveQuietly();
                });
        });
    }

    public function down(): void
    {
        User::query()->get(['id', 'permissions'])->each(function (User $user): void {
            $remaining = array_values(array_diff($user->permissions ?? [], self::PERMISSIONS));

            if ($remaining !== ($user->permissions ?? [])) {
                $user->permissions = $remaining;
                $user->saveQuietly();
            }
        });
    }
};
