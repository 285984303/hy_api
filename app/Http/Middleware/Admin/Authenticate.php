<?php

namespace App\Http\Middleware\Admin;

use Closure;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = 'admin')
    {
//        return response( $request->wantsJson() ? 'true': 'false',200);
//        die();
        if (auth($guard)->guest()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            } else {
                return redirect()->guest('admin/login?url='.$request->url());
            }
        }
        return $next($request);
    }
}
