<?php

namespace core\lib\auth;


/**
 *
 * Class Auth
 * @package core\lib\auth
 */
class Auth {

    /**
     * 实例
     * @var array
     */
    protected $guard    = [];

    /**
     * @var null
     */
    protected $auth     = null;

    /**
     * @var null
     */
    protected $driver   = null;

    /**
     * @var null
     */
    protected $model    = null;

    /**
     * @var null
     */
    protected $provider = null;

    protected $guards = [
        'web' => [
            'driver'   => 'session',
            'provider' => 'user',
            'model'    => \core\src\model\user\UserModel::class,
        ],
        'user' => [
            'driver'   => 'jwt',
            'provider' => 'user',
            'model'    => \core\src\model\user\UserModel::class,
        ],
        'admin' => [
            'driver'   => 'jwt',
            'provider' => 'admin',
            'model'    => \core\src\model\auth\AdminModel::class,
        ],
        'adminSession' => [
            'driver'   => 'session',
            'provider' => 'admin',
            'model'    => \core\src\model\auth\AdminModel::class,
        ],
    ];

    public function __construct () {
        
    }

    /**
     *
     * @param null $guard
     */
    public function guard ($guard = null) {
        return $this->resolve($guard);
    }

    /**
     * 获取当前 guard
     * @param $guard
     * @return mixed
     */
    public function resolve ($guard) {
        $this->model = $this->guards[$guard]['model'];
        if (!isset($this->guard[$guard]) || is_null($this->guard[$guard])) {
            $guard_class = "\\core\\lib\\auth\\guard\\" . ucfirst($this->guards[$guard]['driver']) . 'Guard';
            $this->guard[$guard] = new $guard_class($this->guards[$guard], $guard);
        }
        return $this->guard[$guard];
    }
}
