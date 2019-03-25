<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class LoginAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json([
                'code'    => 2001,
                'message' => '请登录',
                'data'    => (object)[],
            ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }

        return $next($request);
    }
}
