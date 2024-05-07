<?php

namespace core\components\guard\driver;


use core\src\addon\auth\app\model\auth\AdminModel;
use core\exception\FanException;
use think\facade\Cookie;

/**
 * 后台session登录验证
 * Class Admin
 * @package core\driver\auth\guard\driver
 */
class Admin extends BaseGuard {

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    public function initialize(array $config) {
        $this->provider = $this->guards[$this->name]['provider'];
        $this->model    = new AdminModel();
    }

    /**
     * 获取用户信息
     * @param bool $failException
     * @return mixed|null
     * @throws FanException
     */
    public function user (bool $failException = false) {
        if ($this->user !== null)  return $this->user;
        $id = session($this->getName());
        if (!is_null($id)) $this->user = $this->model->find($id);
        if (is_null($this->user) && !is_null($recaller = $this->recaller())) {
            $this->user = $this->userFromRecaller($recaller);
            if ($this->user) $this->login($this->user, true);
        }
        if (is_null($this->user) && $failException) {
            throw new FanException('请登录后再继续操作');
        } else if ($this->user) {
            $this->getProvider()->checkUser($this->user);
        }
        return $this->user;
    }

    /**
     * 用户id
     * @param bool $failException
     * @return null
     * @throws FanException
     */
    public function id (bool $failException = false) {
        $user = $this->user($failException);
        return $user ? $user->id : null;
    }

    /**
     * 验证
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
            return $this;
        }
        // 登录失败
        $this->getProvider()->loginFail($user);
    }

    /**
     * 登录
     * @param \think\Model $user
     * @param false $remember
     * @return $this
     */
    public function login (\think\Model $user, $remember = false) {
        $user = $user->isEmpty() ? null : $user;
        $this->getProvider()->checkUser($user);
        $this->user = $user;
        session($this->getName(), $user->id);
        if ($remember) {
            $this->ensureRememberTokenIsSet($user);
            $this->responseCookie($user);
        }
        $this->getProvider()->loginSuccess($user);
        return $this;
    }

    /**
     * 通过id登录
     * @param $id
     * @param false $remember
     * @return $this
     */
    public function loginUsingId ($id, $remember = false) {
        $user = $this->model->findOrFail($id);
        $this->login($user, $remember);
        return $this;
    }


    /**
     * 获取token
     * @return string|void
     */
    public function getToken () {
        return session($this->getName());
    }

    /**
     * 退出
     */
    public function logout () {
        session($this->getName(), null);
        Cookie::delete($this->getRecallerName());
        if (!is_null($this->user) && !empty($this->user->getRememberToken())) $this->cycleRememberToken($this->user);
        $this->user = null;
    }

    /**
     * 获取token
     * @return string
     */
    public function getName() {
        return $this->provider . ':login:' . 'user_id';
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
     * @param $funcname
     * @param $arguments
     * @return mixed
     */
    public function __call ($funcname, $arguments) {
        return $this->getProvider()->{$funcname}(...$arguments);
    }
}
