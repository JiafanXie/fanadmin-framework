<?php

namespace core\provider;


use core\exception\FanException;
use think\Facade;
use think\helper\Str;

/**
 * 实例化驱动类
 * Class Drive
 * @package core\drive
 */
class Driver extends Facade
{
    /**
     * @var string 类名称
     */
    protected $name;

    /**
     * @var 类命名空间
     */
    protected $namespace;

    /**
     * @var 类
     */
    protected $class;

    /**
     * @var array 配置
     */
    protected $config;

    public function __construct (string $name = '', array $config = []) {
        if (is_array($name)) {
            $config = $name;
            $name   = null;
        }
        if ($name) $this->name = $name;
        $this->config = $config;
    }

    /**
     * 创建实例对象
     * @param string $type
     * @return object|\think\DbManager
     * @throws FanException
     */
    public function createDriver (string $type) {
        $class = $this->getDriverClass($type);
        return $this->createFacade($class, [
            $this->name,
            $this->config,
        ], true);
    }

    /**
     * 获取类
     * @param string $type
     * @return string
     * @throws FanException
     */
    public function getDriverClass (string $type) {
        if ($this->namespace || str_contains($type, '\\')) {
            $class = str_contains($type, '\\') ? $type : $this->namespace . $type;
            if (class_exists($class)) {
                return $class;
            } else {
                $class = str_contains($type, '\\') ? $type : $this->namespace . Str::studly($type);
                if (class_exists($class)) {
                    return $class;
                }
            }
        }
        throw new FanException("Driver [$type] not found.");
    }

    /**
     * 通过装载器获取实例
     * @return 类|object|\think\DbManager
     * @throws FanException
     */
    public function getDriver () {
        if (empty($this->class)) {
            $this->name = $this->name ?: $this->getDefaultDriver();
            if (!$this->name) {
                throw new FanException(sprintf(
                    'could not find driver [%s].', static::class
                ));
            }
            $this->class = $this->createDriver($this->name);
        }
        return $this->class;
    }

    /**
     * 动态调用
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call ($method, $arguments) {
        return $this->getDriver()->{$method}(...$arguments);
    }
}