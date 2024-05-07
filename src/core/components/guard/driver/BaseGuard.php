<?php

namespace core\components\guard\driver;


use core\base\driver\BaseDriver;

/**
 * 登录验证基类
 * Class BaseGuard
 * @package core\drive\auth\guard
 */
abstract class BaseGuard extends BaseDriver {
    /**
     * @var null 权限
     */
    protected $auth     = null;

    /**
     * @var null 模型容器
     */
    protected $provider = null;

    /**
     * @var 容器类
     */
    protected $providerClass;

    /**
     * @var null 模型
     */
    protected $model    = null;

    /**
     * @var 用户信息
     */
    protected $user;

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
     *
     * @var \string[][]
     */
    public $guards = [
        'webApi' => [
            'provider' => 'user',
            'model'    => \core\model\user\UserModel::class,
        ],
        'web' => [
            'provider' => 'user',
            'model'    => \core\model\user\UserModel::class,
        ],
        'userApi' => [
            'provider' => 'user',
            'model'    => \core\model\user\UserModel::class,
        ],
        'user' => [
            'provider' => 'user',
            'model'    => \core\model\user\UserModel::class,
        ],
        'adminApi' => [
            'provider' => 'admin',
            'model'    => \core\model\auth\AdminModel::class,
        ],
        'admin' => [
            'provider' => 'admin',
            'model'    => \core\model\auth\AdminModel::class,
        ],
    ];

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    public function initialize (array $config) {

    }
}