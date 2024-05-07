<?php

namespace FanAdmin\components\storage;



use FanAdmin\provider\Driver;

/**
 * 上传驱动
 * Class StorageDriver
 * @package FanAdmin\components\storage
 */
class StorageDriver extends Driver {

    /**
     * @var string 空间名
     */
    protected $namespace = '\\FanAdmin\\components\\storage\\driver\\';

    /**
     * @return string 默认驱动
     */
    protected function getDefaultDriver()
    {
        return "Local";
    }
}