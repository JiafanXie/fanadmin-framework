<?php

namespace core\components\guard;



use core\provider\Driver;

/**
 * 登录驱动
 * Class GuardDriver
 * @package core\components\guard
 */
class GuardDriver extends Driver
{
    /**
     * @var string 空间名
     */
    protected $namespace = '\\core\\components\\guard\\driver\\';

    /**
     * @return string 默认驱动
     */
    protected function getDefaultDriver()
    {
        return "AdminApi";
    }
}