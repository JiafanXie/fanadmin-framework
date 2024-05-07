<?php

namespace FanAdmin\middleware;


use FanAdmin\library\JwtAuth\JwtAuth;

/**
 * api 登录接口 token 验证类
 * Class Api
 * @package FanAdmin\middleware
 */
class ApiTokenMiddleware {
    public function handle ($request, \Closure $next) {
        $token = $request->header('token');
        if (!$token) return error('Token is empty',500002);
        $check_token = JwtAuth::checkToken($token);
        if ($check_token['code'] == 1) {
            $request->uid = $check_token['data'];
            return $next($request);
        } else {
            return error('Invalid token',500002);
        }
    }
}