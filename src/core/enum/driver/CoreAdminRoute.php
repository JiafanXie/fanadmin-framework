<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 路由类
 * Class Route
 * @package FanAdmin\enum\driver
 */
class CoreAdminRoute extends BaseEnum
{
    /**
     * 加载路由
     * @param array $data
     * @return bool
     */
    public function data(array $data = [])
    {
        // 系统路由
        $routeList = glob(app()->getRootPath().'FanAdmin\\src\\http\\route\\admin\\' . '*');
        foreach ($routeList as $route) {
            if (file_exists($route)) {
                if (is_file($route)) {
                    include $route;
                }
            }
        }
        return true;
    }
}