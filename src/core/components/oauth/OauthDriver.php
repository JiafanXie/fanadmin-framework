<?php

namespace core\provider\oauth;


use core\provider\Driver;

/**
 * 授权驱动
 * Class OauthDriver
 * @package core\provider\oauth
 */
class OauthDriver extends Driver
{
    /**
     * @var string 空间名
     */
    protected $namespace = '\\core\\provider\\oauth\\';

    /**
     * @return string 默认驱动
     */
    protected function getDefaultDriver()
    {
        return "Weapp";
    }
}