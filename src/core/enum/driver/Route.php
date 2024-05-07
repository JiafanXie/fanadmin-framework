<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 路由类
 * Class Route
 * @package FanAdmin\enum\driver
 */
class Route extends BaseEnum
{
    /**
     * 加载路由
     * @param array $data
     * @return bool
     */
    public function data(array $data)
    {
        $addons = $this->getLocalAddons();
        foreach ($addons as $k => $v) {
            $route_path = $this->getAddonAppPath($v) . DIRECTORY_SEPARATOR . $data['app_type'] . DIRECTORY_SEPARATOR . "route" . DIRECTORY_SEPARATOR . "route.php";
            if (is_file($route_path)) {
                include $route_path;
            }
        }
        return true;
    }
}