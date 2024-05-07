<?php

namespace FanAdmin\base\driver;


/**
 * 驱动基类
 * Class BaseDriver
 * @package FanAdmin\base\driver
 */
abstract class BaseDriver {

    /**
     * @var string 驱动名称
     */
    protected $name;

    /**
     * @var 类
     */
    protected $class;

    /**
     * @var 错误信息
     */
    protected $error;

    public function __construct (string $name, array $config = [], $class = null) {
        $this->name = $name;
        $this->initialize($config);
        $this->class = $class;
    }

    /**
     * 设置错误信息
     * @param string|null $error
     * @return false
     */
    protected function setError (?string $error = null) {
        $this->error = $error;
        return false;
    }

    /**
     * @return 获取错误信息
     */
    public function getError() {
        $error = $this->error;
        $this->error = null;
        return $error;
    }

    /**
     * 初始化
     * @param array $config
     * @return mixed
     */
    abstract protected function initialize (array $config);

}