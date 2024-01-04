<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCallback
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        //判断IP
        $trustIp = [
            '127.0.0.1',
            '172.18.0.1',
            '16.163.252.193',
            '172.30.113.36',
        ];

        $ip = getClientIp();
        if (!in_array($ip,$trustIp)){
            exit('非信任请求');
        }
        return $next($request);
    }
}
