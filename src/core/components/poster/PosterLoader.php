<?php
// +----------------------------------------------------------------------
// | Niucloud-admin 企业快速开发的saas管理平台
// +----------------------------------------------------------------------
// | 官方网址：https://www.niucloud-admin.com
// +----------------------------------------------------------------------
// | niucloud团队 版权所有 开源版本可自由商用
// +----------------------------------------------------------------------
// | Author: Niucloud Team
// +----------------------------------------------------------------------

namespace FanAdmin\poster;

use FanAdmin\loader\Loader;
use FanAdmin\sms\BaseSms;

/**
 * @see PosterLoader
 * @package think\facade
 * @mixin BasePoster
 * @method  string|null createPoster(array $poster, string $dir, string $file_path) 创建海报
 */
class PosterLoader extends Loader
{


    /**
     * 空间名
     * @var string
     */
    protected $namespace = '\\FanAdmin\\poster\\';

    protected $config_name = 'poster';

    /**
     * 默认驱动
     * @return mixed
     */
    protected function getDefault()
    {
        return config('poster.default');
    }


}