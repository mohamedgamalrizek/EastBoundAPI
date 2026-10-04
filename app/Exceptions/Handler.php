<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        // Report exceptions
        $this->reportable(function (Throwable $e) {
            // You can send to Sentry, Bugsnag, Slack, etc.
        });

        // Renderable exceptions (optional, most logic should move to bootstrap/app.php)
        $this->renderable(function (Throwable $e, $request) {
            // Example:
            // if ($e instanceof \Illuminate\Auth\AuthenticationException) {
            //     return response()->json(['error' => 'Unauthenticated'], 401);
            // }
        });
    }
}
