<?php

namespace FanAdmin\middleware;


/**
 * 权限验证类
 * Class Auth
 * @package FanAdmin\middleware
 */
class AdminAuthMiddleware {
    public function handle ($request, \Closure $next) {
        $check_role_service = new AuthService();
        $check_role_service->checkRole($request);
        return $next($request);
    }
}