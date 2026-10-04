<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XSS
{

    protected $except = [
        'admin/profile/settings/account/update',
        'admin/user/store',
        'admin/user/update',
        'admin/todo/store',
        'admin/todo/update*',
        'admin/settings/update-settings',
        'admin/settings/mail-settings/update'
    ];
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if(!request()->is($this->except)){
            // Only strings are stripped. $request->all() also carries the
            // uploaded files, and strip_tags() on an UploadedFile coerces it to
            // its temp path string — merging that back replaced every upload
            // with a plain string, so every repository downstream that called
            // ->getClientOriginalExtension() blew up and the form fell into its
            // catch block as "something went wrong". Logos, favicons, avatars,
            // visa scans: anything posted through a route behind this
            // middleware silently failed to upload.
            $input = $request->all();
            array_walk_recursive($input, function(&$input){
                if (is_string($input)) {
                    $input = strip_tags($input);
                }
            });
            $request->merge($input);
        }

        return $next($request);
    }
}
