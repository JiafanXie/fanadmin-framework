<?php

namespace FanAdmin\middleware;


use thans\jwt\exception\TokenExpiredException;
use thans\jwt\exception\TokenBlacklistGracePeriodException;
use thans\jwt\exception\TokenBlacklistException;
use thans\jwt\exception\TokenInvalidException;
use thans\jwt\middleware\BaseMiddleware;
use FanAdmin\provider\Auth;
use core\exception\FanException;

/**
 * admin 接口 token 登录验证类
 * Class Admin
 * @package core\middleware
 */
class AdminTokenMiddleware extends BaseMiddleware {
    public function handle ($request, \Closure $next, $guard = null) {
        try {
            $user  = Auth::guard($guard)->user();
            if (!$user) {
              throw (new \core\exception\FanException)->loginError();
            }
            $request->adminInfo = $user;
            $request->adminId   = $user->id??0;
            $request->roleId    = $user->role_id??0;
        } catch (TokenExpiredException $e) {
            try {
                $token = $this->auth->refresh();
                session('header_authorization', $token);
                // 重新登录，保证这次请求正常进行
                $payload = $this->auth->auth(false);
                Auth::guard($guard)->loginUsingId($payload['uid']->getValue());
            } catch (TokenBlacklistException $e) {
                throw (new \core\exception\FanException)->returntnMessage('您还没有登录，请先登录2', 403, FanException::LOGIN_ERROR);
            } catch (TokenBlacklistGracePeriodException $e) { // 捕获黑名单宽限期
                throw (new \core\exception\FanException)->returntnMessage('您还没有登录，请先登录3', 403, FanException::LOGIN_ERROR);
            } catch (TokenExpiredException $e) {
                throw (new \core\exception\FanException)->returntnMessage('您的登录已过期, 请重新登录1', 1, FanException::LOGIN_ERROR);
            }
        } catch (TokenBlacklistException $e) {
            throw (new \core\exception\FanException)->returntnMessage('账号已下线,请重新登录', 1, FanException::LOGIN_ERROR);
        } catch (TokenBlacklistGracePeriodException $e) { // 捕获黑名单宽限期
            throw (new \core\exception\FanException)->returntnMessage('您还没有登录，请先登录5', 1, FanException::LOGIN_ERROR);
        } catch (TokenInvalidException $e) { // token 无效
            throw (new \core\exception\FanException)->returntnMessage('令牌无效', 1, FanException::LOGIN_ERROR);
        }
        return $next($request);
    }
}
