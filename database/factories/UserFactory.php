<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use App\Enums\Gender;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{

    protected $model = User::class;

    /**
     * `permissions` is a snapshot column, independent of `role_id` — but the
     * definition() below has no visibility into a `role_id` override passed
     * to create()/make(), since Laravel merges those in afterward. Without
     * this, overriding role_id (e.g. `User::factory()->create(['role_id' =>
     * $agentRole->id])`) leaves `permissions` pointing at whatever random
     * role definition() happened to pick — the user ends up with the right
     * role but the wrong permissions, and every `hasPermission:` route 403s
     * unpredictably depending on random role order.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            $user->permissions = Role::find($user->role_id)?->permissions ?? $user->permissions ?? [];
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $role = $this->role();

        return [

            'name'          => $this->faker->unique()->name,
            'date_of_birth'           => $this->faker->date(),
            'email'         => $this->faker->unique()->safeEmail,
            'phone'         => $this->faker->unique()->phoneNumber,
            'nid_number'    => $this->faker->unique()->numberBetween(1000000000, 9999999999),
            'address'       => $this->faker->address,

            'password'      => Hash::make('12345678'),
            'gender'        => Gender::MALE,

            'role_id'       => $role->id,
            'permissions'   => $role->permissions ?? [],

            // 'image_id'      => DB::table('uploads')->insertGetId(['original' => 'backend/images/avatar/user-profile.png']),

        ];
    }

    /**
     * A role for the new user.
     *
     * Tests that use RefreshDatabase start from an empty `roles` table, so the
     * factory creates a minimal fallback rather than dereferencing null — that
     * was making every Jetstream/Fortify scaffold test fail.
     */
    private function role(): Role
    {
        return Role::inRandomOrder()->first(['id', 'name', 'permissions'])
            ?? Role::create([
                'name'        => 'Member',
                'slug'        => 'member',
                'permissions' => ['dashboard_read', 'profile_read', 'profile_update', 'password_update'],
            ]);
    }
}
