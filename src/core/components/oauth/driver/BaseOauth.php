<?php

namespace core\lib\oauth;


/**
 * 第三方授权基类
 * Class BaseOauth
 * @package core\lib\oauth
 */
abstract class BaseOauth {

    /**
     * @var 配置
     */
    protected $config;

    /**
     * 初始化
     * @param array $config
     * @return void
     */
    protected function initialize (array $config = []) {

    }

    /**
     * 授权
     * @param string|null $code
     * @param array $options
     * @return mixed
     */
    abstract public function oauth (string $code = null, array $options = []);
}