<?php

/*
|--------------------------------------------------------------------------
| Payment Gateways (package-free)
|--------------------------------------------------------------------------
| Each gateway is a self-contained class that talks to the provider's OFFICIAL
| REST API via Laravel's Http client — no third-party SDK packages.
|
| Credentials are NOT here and NOT in .env. They live in the `settings` table
| under a `saas_<gateway>_<field>` key, seeded by
| Modules\Saas\Database\Seeders\PaymentSettingSeeder — which only runs when
| SaaS mode is on. Going live is then a database change, not a redeploy, and
| no secret ever sits in a file that could be copied out of a backup.
|
| 'enabled' — the registry the PaymentManager loads (class list).
| 'fields'  — the credential keys each gateway owns. The seeder creates a row
|             per field, and AbstractGateway::config() reads them back.
*/

use Modules\Saas\Payments\Gateways;

return [

    // Where the user is sent back after paying (gateway-agnostic).
    'default_currency' => 'BDT',

    'enabled' => [
        // Bangladesh
        Gateways\BkashGateway::class,
        Gateways\NagadGateway::class,
        Gateways\SslcommerzGateway::class,
        Gateways\AamarpayGateway::class,
        // India
        Gateways\RazorpayGateway::class,
        Gateways\PayuGateway::class,
        Gateways\CashfreeGateway::class,
        Gateways\InstamojoGateway::class,
        // International
        Gateways\StripeGateway::class,
        Gateways\PaypalGateway::class,
        Gateways\PaystackGateway::class,
        Gateways\FlutterwaveGateway::class,
    ],

    /*
    | Credential fields per gateway. A field listed as 'sandbox' is seeded to
    | '1' (test mode) so a fresh install can never take a real payment by
    | accident; every other field is seeded blank for the platform owner to
    | fill in under the SaaS panel.
    */
    'fields' => [

        /* ---------------- Bangladesh ---------------- */
        'bkash'       => ['sandbox', 'app_key', 'app_secret', 'username', 'password'],
        'nagad'       => ['sandbox', 'merchant_id', 'merchant_number', 'public_key', 'private_key'],
        'sslcommerz'  => ['sandbox', 'store_id', 'store_passwd'],
        'aamarpay'    => ['sandbox', 'store_id', 'signature_key'],

        /* ---------------- India ---------------- */
        'razorpay'    => ['key_id', 'key_secret'],
        'payu'        => ['sandbox', 'merchant_key', 'merchant_salt'],
        'cashfree'    => ['sandbox', 'app_id', 'secret_key'],
        'instamojo'   => ['sandbox', 'api_key', 'auth_token'],

        /* ---------------- International ---------------- */
        'stripe'      => ['secret', 'public'],
        'paypal'      => ['sandbox', 'client_id', 'client_secret'],
        'paystack'    => ['secret_key', 'public_key'],
        'flutterwave' => ['secret_key', 'public_key'],

    ],

];
