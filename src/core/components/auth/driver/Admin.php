<?php

namespace core\components\auth\driver;


use core\exception\FanException;
use core\lib\ratelimiter\RateLimiter;

/**
 * admin驱动
 * Class Admin
 * @package core\components\auth\driver
 */
class Admin {

    /**
     * @var null auth
     */
    protected $auth = null;

    /**
     * @var array 当前用户的所有角色
     */
    protected $roles = [];

    /**
     * @var int 登录最大尝试次数
     */
    protected $loginMaxAttempts = 5;

    /**
     * @var int 锁定时间
     */
    protected $loginDecaySeconds = 144;

    /**
     * @var RateLimiter
     */
    protected $rateLimiter;

    public function __construct ($auth) {
        $this->auth        = $auth;
        $this->rateLimiter = new RateLimiter($this);
        // 动态获取锁定时间和登录错误次数
    }

    /**
     * 获取缓存
     * @param $user
     * @return string
     */
    public function cacheKey ($user) {
        $id = is_int($user) ? $user : $user->id;
        return 'admin:' . $id . ':login';
    }

    /**
     * 检测用户状态
     * @param $user
     * @throws FanException
     */
    public function checkUser ($user) {
        if (!$user) throw (new FanException)->returntnMessage('登录失败，请重新登录', 1, FanException::LOGIN_ERROR);
        if ($user->status == 'disabled') {
            throw new FanException('账号已被禁用');
        }
    }

    /**
     * 检测登录失败次数
     * @param $user
     * @throws FanException
     */
    public function rateLimiter ($user) {
        if ($user && $this->rateLimiter->tooManyAttempts($this->cacheKey($user), $this->loginMaxAttempts, $this->loginDecaySeconds)) {
            $decay = $this->rateLimiter->availableIn($this->cacheKey($user));
            $decayMinutes = ceil($decay / 60);
            throw new FanException('账号已锁定，请于 ' . $decayMinutes . ' 分钟后重试');
        }
    }

    /**
     * 登录成功
     * @param $user
     * @return bool
     */
    public function loginSuccess ($user) {
        $cacheKey = $this->cacheKey($user);
        // 清空登录失败缓存
        $this->rateLimiter->clear($cacheKey);
        // 清空数据库登录失败次数
        $user->login_fail = 0;
        $user->login_time = time();
        $user->login_ip = request()->ip();
        $user->save();
        return true;
    }

    /**
     * 登录失败
     * @param $user
     * @throws FanException
     */
    public function loginFail ($user) {
        $cacheKey = $this->cacheKey($user);
        // 登录失败，记录失败次数
        $this->rateLimiter->hit($cacheKey, $this->loginDecaySeconds);
        // 更新数据库登录失败次数
        $user->inc('login_fail')->update();
        $left = $this->rateLimiter->retriesLeft($cacheKey, $this->loginMaxAttempts);
        if ($left > 0) {
            $message = '密码错误,您还可以尝试 ' . $left . ' 次';
        } else {
            $message = '您的尝试次数过多,账号已锁定';
        }
        throw new FanException($message);
    }

    /**
     * 当前管理员是否是超级管理员
     * @return bool
     */
    public function isSuper () {
        if ($this->auth->user()->role_id === 1) {
            return true;
        }
        return false;
    }
}
