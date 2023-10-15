<?php

namespace App\Http\Middleware;

/**
 * 跨域中间件
 */
class EnableCrossRequestMiddleware
{
    /**
     * Handle a incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';
        header('Access-Control-Allow-Origin:' . $origin);
        header('Access-Control-Allow-Methods:GET, POST, PATCH, PUT, DELETE');
        header('Access-Control-Allow-Headers:Origin, Content-Type, Cookie, X-CSRF-TOKEN, Accept, Authorization, X-XSRF-TOKEN');
        header('Access-Control-Allow-Credentials:true');
        return $next($request);
    }
}