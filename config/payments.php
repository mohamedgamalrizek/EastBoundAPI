<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Online payment gateways
    |--------------------------------------------------------------------------
    |
    | Classes registered with PaymentManager. Credentials are NOT configured
    | here — the agency enters them under Settings → Payment Gateways, so going
    | live needs no deploy. Adding a provider means writing the gateway class,
    | listing it here, and adding its fields to the settings page + seeder.
    |
    */

    'gateways' => [
        App\Services\Payments\Gateways\BkashGateway::class,
        App\Services\Payments\Gateways\SslcommerzGateway::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Intent lifetime
    |--------------------------------------------------------------------------
    |
    | A pending intent older than this is treated as abandoned and is no longer
    | confirmable — a stale callback cannot settle a booking days later.
    |
    */

    'intent_ttl_minutes' => 60,

];
