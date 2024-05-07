<?php

namespace core\components\auth\driver;


use core\exception\FanException;
use core\src\model\user\UserModel;
use core\lib\ratelimiter\RateLimiter;

/**
 * user驱动
 * Class User
 * @package core\components\auth\driver
 */
class User {

    /**
     * @var null auth
     */
    protected $auth = null;

    /**
     * @var array 当前用户的所有角色
     */
    protected $roles = [];

    /**
     * @var array 当前用户的所有权限
     */
    protected $rules = [];

    /**
     * @var int 登录最大尝试次数
     */
    protected $loginMaxAttempts = 5;

    /**
     * @var int 锁定时间
     */
    protected $loginDecaySeconds = 36;

    /**
     * @var RateLimiter
     */
    protected $rateLimiter;

    public function __construct ($auth) {
        $this->auth        = $auth;
        $this->rateLimiter = new RateLimiter($this);
    }

    /**
     * 获取缓存
     * @param $user
     * @return string
     */
    public function cacheKey ($user) {
        $id = is_int($user) ? $user : $user->id;
        return 'user:' . $id . ':login';
    }

    /**
     * 检测用户状态
     * @param $user
     * @return bool
     * @throws FanException
     */
    public function checkUser ($user) {
        if (!$user) throw (new FanException)->returntnMessage('未找到该用户', 1, FanException::LOGIN_ERROR);
        if ($user->status == 'disabled') {
            throw new FanException('账号已被禁用');
        }
        return true;
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
        $user->login_ip   = request()->ip();
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
     * 用户注册
     * @param array $params
     * @param string $from
     * @return array|UserModel|mixed|\think\Model
     * @throws FanException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function register ($params = [], $from = '') {
        $username = $params['username'] ?? null;
        $mobile   = $params['mobile']   ?? null;
        $email    = $params['email']    ?? null;
        $password = $params['password'] ?? null;
        if ($username || $mobile || $email) {
            $user = UserModel::where(
                function ($query) use ($mobile, $email, $username) {
                    if ($mobile) $query->whereOr('mobile', $mobile);
                    if ($email) $query->whereOr('email', $email);
                    if ($username) $query->whereOr('username', $username);
                })
                ->find();
            if ($user) throw new FanException('账号已注册，请直接登录');
        }
        $default = $this->getUserDefaultConfig();
        $create  = UserModel::create([
            'username' => $username ?? null,
            'nickname' => !empty($params['nickname']) ? $params['nickname'] : $default['nickname'] . genRandomStr(8),
            'mobile'   => $mobile ?? null,
            'avatar'   => $params['avatar'] ?? '',
            'email'    => $email ?? null,
            'app'      => app('http')->getName(),
        ]);

        $user = UserModel::findOrFail($create->id);
        // 存在密码， 处理密码加密
        if ($password) {
            // 每次修改密码，都重新生成 salt
            $this->setPassword($user, $password);
        }
        // 用户注册之后业务处理
        event('user.RegisterAfter', ['user' => $user]);
        return $user;
    }

    /**
     * 重置密码
     * @param array $params
     * @return bool
     * @throws FanException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function resetPassword (array $params) {
        $mobile   = $params['mobile'] ?? null;
        $email    = $params['email'] ?? null;
        $password = $params['password'] ?? null;
        if (!$params['mobile'] && !$params['email']) throw new FanException('参数错误');
        $user = UserModel::where(
            function ($query) use ($mobile, $email) {
                if ($mobile) $query->whereOr('mobile', $mobile);
                if ($email) $query->whereOr('email', $email);
        })->find();
        if (!$user) throw new FanException('账号不存在');
        $this->setPassword($user, $password);
        $user->save();
        return true;
    }

    /**
     * 修改密码
     * @param $old_password
     * @param $password
     * @return bool
     * @throws FanException
     */
    public function modifyPassword ($old_password, $password) {
        $user = $this->auth->user(true);
        if ($user->encryptPassword($old_password, $user->salt) != $user->password) throw new FanException('旧密码不正确');
        $this->setPassword($user, $password);
        $user->save();
        return true;
    }

    /**
     * 获取用户默认值配置
     * @return string[]
     */
    private function getUserDefaultConfig () {
        return [
            'nickname' => '用户'
        ];
    }

    /**
     * 加密密码
     * @param $user
     * @param $password
     */
    private function setPassword ($user, $password) {
        $salt = genRandomStr(4);
        $user->salt = $salt;
        $user->password = $user->encryptPassword($password, $salt);
    }
}
