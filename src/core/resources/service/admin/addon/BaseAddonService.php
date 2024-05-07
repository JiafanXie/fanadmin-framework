<?php

namespace FanAdmin\src\http\service\admin\addon;

use FanAdmin\base\service\BaseService as Base;
use FanAdmin\exception\CommonException;

/**
 * 插件基类
 * Class BaseAddonService
 * @package FanAdmin\lib\addon
 */
class BaseAddonService extends Base
{
    /**
     * @var 插件根目录
     */
    protected $addonPath;

    /**
     * @var string 项目目录
     */
    protected $rootPath;

    /**
     * @var string 缓存标识名称
     */
    public static $cacheTagName = 'addonCash';

    public function __construct()
    {
        parent::__construct();
        $this->rootPath  = dirname(root_path()) . DIRECTORY_SEPARATOR;
        $this->addonPath = root_path() . 'addon' . DIRECTORY_SEPARATOR;
    }

    /**
     * 插件基础配置信息
     * @param string $addon
     * @return array|mixed
     */
    public function getAddonConfig(string $addon)
    {
        $path         = $this->addonPath . $addon . DIRECTORY_SEPARATOR . 'info.json';
        $resourcePath = $this->addonPath . $addon . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
        if (is_file($path)) {
            $jsonString    = file_get_contents($path);
            $info          = json_decode($jsonString, true);
            $info['icon']  = $resourcePath . 'icon.png';
            $info['cover'] = $jsonString . 'cover.png';
        }
        return $info ?? [];
    }

    /**
     * 插件配置文件目录
     * @param string $addon
     * @return string
     */
    public function getAddonConfigPath(string $addon)
    {
        return $this->addonPath . $addon . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR;
    }

    /**
     * 插件定义的package目录
     * @param string $addon
     * @return string
     */
    public function geAddonPackagePath(string $addon)
    {
        return $this->addonPath . $addon . DIRECTORY_SEPARATOR . 'package' . DIRECTORY_SEPARATOR;
    }

    /**
     * 读取json文件转化成数组返回
     * @param string $json_file_path
     * @return array|mixed
     */
    public function jsonFileToArray(string $json_file_path)
    {
        if (file_exists($json_file_path)) {
            $content_json = @file_get_contents($json_file_path);
            return json_decode($content_json, true);
        } else
            return [];
    }

    /**
     * 读取json文件转化成数组返回
     * @param array $content
     * @param string $file_path
     * @return bool
     */
    public function writeArrayToJsonFile(array $content, string $file_path)
    {
        $content_json = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $content_json = preg_replace('/\[\]/', '{}', $content_json);
        $result = @file_put_contents($file_path, $content_json);
        if (!$result) {
            throw new CommonException($file_path . '文件不存在或者权限不足');
        }
        return true;
    }
}
