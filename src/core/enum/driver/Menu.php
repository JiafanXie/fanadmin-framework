<?php

namespace FanAdmin\enum\driver;


use FanAdmin\base\enum\BaseEnum;

/**
 * 菜单类
 * Class Menu
 * @package FanAdmin\enum\driver
 */
class Menu extends BaseEnum
{
    /**
     * 加载菜单
     * @param array $data
     * @return array
     */
    public function data(array $data): array
    {
        $menu_path = $this->getAddonDictPath($data['addon']) . "menu" . DIRECTORY_SEPARATOR . $data['app_type'] . ".php";
        if (is_file($menu_path)) {
            return include $menu_path;
        }
        return [];
    }
}