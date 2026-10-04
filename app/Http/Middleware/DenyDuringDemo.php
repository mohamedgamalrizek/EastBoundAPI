<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks a write route while APP_DEMO=true, so the public demo can be
 * browsed and clicked through without a visitor breaking Settings, System
 * Update or the app Language list for every other visitor.
 */
class DenyDuringDemo
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.demo')) {
            return $next($request);
        }

        $message = 'This action is disabled in the public demo.';

        if ($request->expectsJson()) {
            return response()->json(['status' => false, 'message' => $message], 403);
        }

        return redirect()->back()->with('danger', $message);
    }
}
