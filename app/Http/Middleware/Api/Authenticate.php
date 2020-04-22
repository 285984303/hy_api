<?php

namespace App\Http\Middleware\Api;

use Closure;

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
    public function handle($request, Closure $next, $guard = 'web')
    {
        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Origin' => env('APP_URL','http://localhost'),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin'
        ];
        //auth($guard)->loginUsingId(90);
        if (auth($guard)->guest()) {
            return redirect()->guest('/');
            /*if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401, $headers);
            } else {
                return response('Unauthorized', 401, $headers);
            }*/
        }
        return $next($request);
    }
}
