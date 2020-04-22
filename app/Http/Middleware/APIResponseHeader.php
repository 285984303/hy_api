<?php namespace App\Http\Middleware;
/**
 * Created by PhpStorm.
 * User: Will
 * Date: 8/1/16
 * Time: 6:21 PM
 */

use Closure;
use Illuminate\Contracts\Routing\ResponseFactory;

class APIResponseHeader {

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        debugbar()->disable();
        // ALLOW OPTIONS METHOD
        $headers = [
            'Access-Control-Allow-Origin' => env('APP_URL','http://localhost'),
            // 'Access-Control-Allow-Origin'=>'http://'.request()->getHost(),
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Allow-Methods'=> 'POST, GET, OPTIONS, PUT, DELETE',
            'Access-Control-Allow-Headers'=> 'Content-Type, X-Auth-Token, Origin'
        ];
        if($request->getMethod() == "OPTIONS") {
            // The client-side application can set only headers allowed in Access-Control-Allow-Headers
            return response('OK', 200, $headers);
        }

        $response = $next($request);
        foreach($headers as $key => $value)
            $response->headers->set($key, $value);

        return $response;
    }

}
