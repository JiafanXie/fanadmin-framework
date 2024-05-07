<?php

namespace FanAdmin\enum;


use FanAdmin\provider\Driver;

/**
 * 数据获取类
 * Class DictLoader
 * @package FanAdmin\dict
 */
class EnumLoader extends Driver
{
    /**
     * @var string 空间名
     */
    protected $namespace = '\\FanAdmin\\enum\\driver\\';

    /**
     * @return string 默认驱动
     */
    protected function getDefault()
    {
        return "Event";
    }
}