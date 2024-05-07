<?php

namespace core\provider;


use think\Facade;
use core\lib\Redis as RedisManager;

/**
 * Class Redis
 * @package core\facade
 */
class Redis extends Facade {
    /**
     * 获取当前Facade对应类名（或者已经绑定的容器对象标识）
     * @access protected
     * @return string
     */
    protected static function getFacadeClass () {
        return 'redis';
    }
}
