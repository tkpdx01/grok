<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiAuth
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
        if (env('SIGN_ENABLE')===true){
            $now = time();
            $time = $request->post('time',0);
            $sign = $request->post('sign','');
            if (empty($time) || abs($now-$time)>300 || empty($sign)){
                return responseAuthError('请求已过期');
            }

            $params = $request->except(['sign']);
            ksort($params);
            $str = '';
            foreach($params as $k => $v) {
                // $v 为 array 递归拼接
                $str .= $k . $v;
            }
            $str .= env('SIGN_KEY', '1234567890');
            $sign = md5($str);

            if ($sign != $request->post('sign')) {
                return responseAuthError('非法请求');
            }
        }

        // 配置不执行过滤转换参数项，如 env 配置: XSS_EXCEPT=article_contents,html_contents
        $param_except_string = config('const.xss_filter_param_except');
        $param_except = [];
        if (!empty($param_except_string)) {
            $param_except = explode(',', $param_except_string);
        }
        $input = $request->except($param_except);
        array_walk_recursive($input, function (&$input) {
            $input = strip_tags($input);  // 清除标签内容
        });
        $request->merge($input);
        return $next($request);
    }
}
