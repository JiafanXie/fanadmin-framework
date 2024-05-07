<?php

namespace core\provider\pay;


use core\provider\Driver;

/**
 * @see PayDriver
 * @package think\facade
 * @mixin B
 * @method  string|null upload(string $dir) 附件上传
 * @method  array fetch(string $url, ?string $key) 抓取远程附件
 * @method  mixed delete(string $file_name) 附件删除
 */
class PayDriver extends Driver
{
    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\core\\pay\\';

    protected $config_name = 'pay';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefault()
    {
        return config('pay.default');
    }
}