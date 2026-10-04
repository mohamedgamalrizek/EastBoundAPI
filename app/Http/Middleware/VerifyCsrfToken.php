<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Payment providers POST their result straight to this URL from their
        // own servers, with no session and no token of ours to send. The route
        // is safe without CSRF because it trusts nothing in the request: the
        // gateway re-validates the transaction against the provider's API
        // before anything is settled.
        'payment/callback/*',
    ];
}
