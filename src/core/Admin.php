<?php

namespace FanAdmin;


/**
 * Class Admin
 * @package FanAdmin
 */
class Admin
{
    /**
     * @var string
     */
    public static $addonPath = 'addon';

    /**
     * 版本
     */
    public const version = '1.0.0';

    /**
     * @return string
     */
    public static function directory(): string
    {
        return app()->getRootPath() . self::$addonPath . DIRECTORY_SEPARATOR;
    }

    /**
     * 加载admin路由
     */
    public function loadAdminRoute()
    {
        require(__DIR__.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR.'admin.php');
    }

    /**
     * 获取全部插件目录
     */
    public static function getAddonDirectory():array
    {
        $addon = [];
        foreach (scandir(self::directory()) as $dir) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            $addon[] = root_path('app/'.$dir);
        }
        return $addon;
    }

    /**
     * 获取插件路由
     */
    public static function getAddonRoute()
    {
        $service = [];
        foreach (scandir(self::directory()) as $dir) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            if (is_file(self::directory() . $dir . DIRECTORY_SEPARATOR."ServiceProvider.php")) {
                $service[] = "\app\\{$dir}\ServiceProvider";
            }
        }
        return $service;
    }

    /**
     * 获取插件服务类
     * @return array
     */
    public static function getAddonService()
    {
        $service = [];
        foreach (scandir(self::directory()) as $dir) {
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            if (is_file(self::directory() . $dir . DIRECTORY_SEPARATOR."ServiceProvider.php")) {
                $service[] = "\app\\{$dir}\ServiceProvider";
            }
        }
        return $service;
    }

    /**
     * 构建后台菜单
     */
    public static function buildAdminMenu()
    {
        $menuFile = __DIR__.'/menu.php';
        $menuTree = require($menuFile);
        $menu = MenuService::buildMenuList($menuTree);
        Permission::where('plugin','admin')->delete();
        foreach($menu as $v){
            $v['plugin'] = 'admin';
            Permission::create($v);
        }
    }

    /**
     * 发布后台静态文件
     */
    public static function publishAdminStatic()
    {

    }
}