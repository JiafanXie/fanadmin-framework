<?php

namespace core\lib\auth\guard;


use thans\jwt\facade\JWTAuth;
use core\exception\FanException;

/**
 * 接口登录
 * Class JwtGuard
 * @package core\lib\auth\guard
 */
class JwtGuard {

    /**
     * 用户模型
     * @var null
     */
    protected $user = null;

    /**
     * token
     * @var null
     */
    protected $token = null;

    /**
     * 实例
     * @var null
     */
    protected $guard = null;

    /**
     * 容器类
     * @var null
     */
    protected $providerClass = null;

    public function __construct ($currentGuard, $guard) {
        $this->guard    = $guard;
        $this->provider = $currentGuard['provider'];
        $this->model    = new $currentGuard['model'];
    }

    /**
     * 用户信息
     * @param bool $failException
     * @return null
     * @throws FanException
     */
    public function user (bool $failException = false) {
        if ($this->user !== null)  return $this->user;
        if (JwtAuth::token() && ($payload = JwtAuth::auth()) && $payload['type'] == $this->provider) $this->user = $this->model->find($payload['uid']);
        if (is_null($this->user) && $failException) {
            throw new FanException('请登录后再继续操作');
        } else if ($this->user) {
            // 检测用户是否正常
            $this->getProvider()->checkUser($this->user);
        }
        return $this->user;
    }

    /**
     * 用户id
     * @param false $failException
     * @return null
     * @throws FanException
     */
    public function id ($failException = false) {
        $user = $this->user($failException);
        return $user ? $user->id : null;
    }

    /**
     * 登录
     * @param array $credentials
     * @param false $remember
     * @return bool
     * @throws FanException
     */
    public function attempt (array $credentials = [], $remember = false) {
        if (empty($credentials['username']) || empty($credentials['password'])) throw new FanException('请输入正确的账号或密码');
        $user = $this->model->where(
            function ($query) use ( $credentials) {
                $query->whereOr('username', $credentials['username']);  
            })
            ->find();
        if (!$user) throw new FanException('您的账号或密码不正确');
        // 失败次数尝试
        $this->getProvider()->rateLimiter($user);
        // 验证密码
        if ($this->model->encryptPassword($credentials['password'], $user->salt) == $user->password) {
            $this->login($user, $remember);
            return true;
        }
        // 登录失败
        $this->getProvider()->loginFail($user);
    }

    /**
     * 登录
     * @param \think\Model $user
     * @param false $remember
     * @return $this
     * @throws FanException
     */
    public function login (\think\Model $user, $remember = false) {
        $user = $user->isEmpty() ? null : $user;
        $this->getProvider()->checkUser($user);
        $this->user = $user;
        $this->getTokenToSession();
        $this->getProvider()->loginSuccess($user);
        return $this;
    }

    /**
     * 通过id登录
     * @param $id
     * @param false $remember
     * @return $this|false
     * @throws FanException
     */
    public function loginUsingId ($id, $remember = false) {
        $user = $this->model->find($id);
        if ($user) {
            $this->login($user, $remember);
            return $this;
        }
        return false;
    }

    /**
     * 退出登录
     */
    public function logout () {
        JwtAuth::invalidate(JwtAuth::token());
        $this->user = null;
    }

    /**
     * 获取session
     * @throws FanException
     */
    private function getTokenToSession () {
        $token = JwtAuth::builder([
            'type' => $this->provider,
            'uid' => $this->id(),
        ]);
        $this->token = $token;
        session('header_authorization', $token);        // 将新的 token 存入 session
    }

    /**
     * 获取token
     * @return null
     */
    public function getToken () {
        return $this->token;
    }

    /**
     * 实例化 provider
     * @return mixed
     */
    public function getProvider () {
        if (!$this->providerClass) {
            $class = "\\core\\lib\\auth\\provider\\" . ucfirst($this->provider);
            $this->providerClass = new $class($this);
        }
        return $this->providerClass;
    }

    /**
     * 转向 provider
     * @param $funcName
     * @param $arguments
     * @return mixed
     */
    public function __call ($funcName, $arguments) {
        return $this->getProvider()->{$funcName}(...$arguments);
    }
}
