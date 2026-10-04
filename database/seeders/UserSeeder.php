<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Models\Customer;
use App\Models\Role;
use App\Models\User;
use App\Repositories\Upload\UploadInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    private $uploadRepo;

    public function __construct(UploadInterface $uploadRepo)
    {
        $this->uploadRepo = $uploadRepo;
    }

    public function run()
    {
        /**
         * One demo user per role so every panel/role can be tested.
         * role_id matches the order roles are created in RoleSeeder.
         * Password for all: 12345678
         */
        $users = [
            ['name' => 'Super Admin',      'email' => 'superadmin@bugbuild.com', 'phone' => '01912938001', 'role_id' => 1],
            ['name' => 'Admin',            'email' => 'admin@bugbuild.com',      'phone' => '01912938002', 'role_id' => 2],
            ['name' => 'Manager',          'email' => 'manager@bugbuild.com',    'phone' => '01912938003', 'role_id' => 3],
            ['name' => 'Operations Staff', 'email' => 'operations@bugbuild.com', 'phone' => '01912938004', 'role_id' => 4],
            ['name' => 'Accountant',       'email' => 'accountant@bugbuild.com', 'phone' => '01912938005', 'role_id' => 5],
            ['name' => 'Support Agent',    'email' => 'support@bugbuild.com',    'phone' => '01912938006', 'role_id' => 6],
            ['name' => 'Travel Agent',     'email' => 'agent@bugbuild.com',      'phone' => '01912938007', 'role_id' => 7],
            ['name' => 'Customer',         'email' => 'customer@bugbuild.com',   'phone' => '01912938008', 'role_id' => 8],
            ['name' => 'SaaS Super Admin', 'email' => 'saas@bugbuild.com',       'phone' => '01912938009', 'role_id' => 9],
            ['name' => 'Staff',            'email' => 'staff@bugbuild.com',      'phone' => '01912938010', 'role_id' => 10],
        ];

        // The SaaS platform owner is only seeded when SaaS mode is on. The role
        // itself stays (kept in RoleSeeder) so role_id ordering never shifts.
        if (! config('saas.enabled')) {
            $users = array_values(array_filter(
                $users,
                fn ($u) => $u['email'] !== 'saas@bugbuild.com'
            ));
        }

        // Production installs create their one administrator in the installer,
        // not predictable demo accounts with shared credentials.
        if (! config('app.demo')) {
            return;
        }

        $customerRoleId = Role::where('slug', 'customer')->value('id');

        foreach ($users as $data) {
            // Re-runnable on a seeded DB: keep existing accounts (and their
            // possibly-changed passwords) instead of violating the unique email.
            if (User::where('email', $data['email'])->exists()) {
                continue;
            }

            $user = new User;
            $user->name = $data['name'];
            $user->email = $data['email'];
            $user->phone = $data['phone'];
            $user->password = Hash::make('12345678');
            $user->gender = Gender::MALE;
            $user->remember_token = Str::random(10);
            $user->nid_number = '33422';
            $user->image_id = $this->uploadRepo->uploadSeederByPath('backend/images/avatar/user.png');
            $user->date_of_birth = '2022-01-01';
            $user->address = 'Mirpur-10, Dhaka-1216';
            $user->role_id = $data['role_id'];

            // Agents earn a percentage of what they sell; a paid booking turns
            // it into a commission automatically. NULL would fall back to the
            // agency default in Settings.
            if (Role::find($data['role_id'])?->name === 'Agent') {
                $user->commission_rate = 7.00;
            }

            // A customer-role login is backed by a `customers` record — the
            // same link website sign-ups create — so seeded demo customers
            // show up on the Customers screen instead of only in Users.
            if ($data['role_id'] === $customerRoleId) {
                $user->customer_id = Customer::firstOrCreate(
                    ['email' => $data['email']],
                    [
                        'name' => $data['name'],
                        'phone' => $data['phone'],
                        'status' => 'active',
                        'notes' => 'Seeded portal customer.',
                    ]
                )->id;
            }
            $user->permissions = Role::find($data['role_id'])->permissions;
            $user->save();
        }
    }
}
