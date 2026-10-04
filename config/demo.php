<?php

/*
|--------------------------------------------------------------------------
| Demo Login Accounts
|--------------------------------------------------------------------------
|
| The quick-login buttons on the sign-in pages. These are showcase accounts
| for a public demo, NOT part of a normal install: nothing here is used unless
| APP_DEMO=true, and each account is additionally checked against the users
| table before it is offered — so a buyer who seeds their own data never sees a
| button for an account that does not exist.
|
| Kept in config rather than inline in a Blade file so an install can change or
| empty the list without editing views.
|
*/

return [

    // Buttons shown on the SaaS platform login when config/saas.php enabled = true.
    'saas' => [
        ['label' => 'Platform Admin', 'email' => env('DEMO_SAAS_EMAIL', 'saas@bugbuild.com'), 'admin' => true],
    ],

    // Buttons shown on a normal single-agency install.
    'accounts' => [
        ['label' => 'Super Admin',  'email' => env('DEMO_SUPERADMIN_EMAIL', 'superadmin@bugbuild.com'), 'admin' => true],
        ['label' => 'Admin',        'email' => env('DEMO_ADMIN_EMAIL',      'admin@bugbuild.com'),      'admin' => true],
        ['label' => 'Manager',      'email' => env('DEMO_MANAGER_EMAIL',    'manager@bugbuild.com'),    'admin' => true],
        ['label' => 'Operations',   'email' => env('DEMO_OPERATIONS_EMAIL', 'operations@bugbuild.com'), 'admin' => true],
        ['label' => 'Accountant',   'email' => env('DEMO_ACCOUNTANT_EMAIL', 'accountant@bugbuild.com'), 'admin' => true],
        ['label' => 'Support',      'email' => env('DEMO_SUPPORT_EMAIL',    'support@bugbuild.com'),    'admin' => true],
        ['label' => 'Travel Agent', 'email' => env('DEMO_AGENT_EMAIL',      'agent@bugbuild.com'),      'admin' => false],
        ['label' => 'Customer',     'email' => env('DEMO_CUSTOMER_EMAIL',   'customer@bugbuild.com'),   'admin' => false],
    ],

];
