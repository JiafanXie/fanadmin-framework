<?php

namespace core\provider\oss;

use core\provider\Driver;
use core\provider\oss\driver\BaseOss;

/**
 * @see OssDriver
 * @package think\facade
 * @mixin BaseOss
 * @method  string|null upload(string $dir) 附件上传
 * @method  array fetch(string $url, ?string $key) 抓取远程附件
 * @method  mixed delete(string $file_name) 附件删除
 * @method  mixed thumb(string $file_path, $thumb_type) 附件删除
 * @method  mixed base64(string $base64_data, ?string $key = null) base文件上传
 */
class OssDriver extends Driver
{
    /**
     * @var string 空间名
     */
    protected $namespace = '\\core\\provider\\oss\\driver\\';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return 'Local';
    }
}