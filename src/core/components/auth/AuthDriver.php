<?php

namespace FanAdmin\components\auth;


use FanAdmin\provider\Driver;

/**
 * 验证驱动
 * Class AuthDriver
 * @package core\components\auth
 */
class AuthDriver extends Driver
{
    /**
     * @var string 空间名
     */
    protected $namespace = '\\core\\components\\auth\\';

    /**
     * @return string 默认驱动
     */
    protected function getDefaultDriver()
    {
        return "Admin";
    }
}