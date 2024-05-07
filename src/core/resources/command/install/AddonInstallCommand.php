<?php

namespace FanAdmin\src\command\install;


use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\Output;

/**
 * 插件安装
 * Class AddonInstallCommand
 * @package FanAdmin\src\command\install
 */
class AddonInstallCommand extends Command {

    protected function configure () {
        $this->setName('install:addon')
            ->addArgument('addon', Argument::OPTIONAL, 'addon name .')
        	->setDescription('安装插件');
    }

    protected function execute (Input $input, Output $output) {
        $plugin = $input->getArgument('addon');
        if (!$plugin) {
            $output->error('请输入插件名称');
        }
        $this->install($plugin);
    	$output->info('插件安装成功！');
    }

    /**
     * 安装插件
     * @param String $plugin
     */
    public static function install (String $plugin) {
        // 执行install.sql
        (new SqlExecute())->execInstallSql($plugin);
        // 执行data.sql
        $dataSql = (new SqlExecute())->execDataSql($plugin);
        // 保存插件信息到数据库
        self::savePlugin($plugin, $dataSql[1]);
        // 菜单导入数据库
        self::buildPluginMenu($plugin);
        // 发布静态文件
        self::publishPluginFile($plugin);
        // 执行构建
        Terminal::execute(root_path('admin'),'npm run build');

    }

    /**
     * 本地应用列表
     */
    public static function plugins () {
        $plugins = [];
        foreach (scandir(base_path()) as $app) {
            if ($app == '.' || $app == '..' || is_file(base_path() . $app) || !file_exists(base_path($app).'plugin.json')) {
                continue;
            }
            $plugins[] = $app;
        }
        return $plugins;
    }

    /**
     * 单个应用详情
     */
    public static function plugin (String $plugin) {
        $pluginFile = base_path($plugin) . 'plugin.json';
        $info = [];
        if (is_file($pluginFile)) {
            $info = json_decode(file_get_contents($pluginFile), true);
            $info['logo'] = self::logo($plugin);
        }
        return $info;
    }

    /**
     * 获取logo
     */
    public static function logo ($plugin) {
        try {
            $logo = base_path($plugin) . 'logo.png';
            if (is_file($logo) && $file = fopen($logo, 'rb', false)) {
                $content = fread($file, filesize($logo));
                fclose($file);
                $base64 = chunk_split(base64_encode($content));
                return 'data:image/png;base64,' . $base64;
            }
        } catch (\ReflectionException $e) {

        }
        return '';
    }

    /**
     * 启用插件
     */
    public static function enable ($plugin) {
        self::buildPluginMenu($plugin);
        PluginModel::where('plugin', $plugin)->update(['status' => 'enable']);
    }

    /**
     * 禁用插件
     */
    public static function disable ($plugin) {
        Permission::where('plugin', $plugin)->delete();
        PluginModel::where('plugin', $plugin)->update(['status' => 'disable']);
    }

    /**
     * 保存安装信息到数据库
     */
    public static function savePlugin ($plugin, $sqlVer) {
        $pluginInfo = self::plugin($plugin);
        $plu =  PluginModel::where('plugin', $plugin)->find();
        if ($plu) {
            $plu->version = $pluginInfo['version'];
            $plu->sql_ver = $sqlVer;
            $plu->save();
            return $plu;
        }
        return PluginModel::create(['plugin' => $plugin, 'name' => $pluginInfo['name'], 'version' => $pluginInfo['version'], 'sql_ver' => $sqlVer]);
    }

    /**
     * 构建插件菜单
     */
    public static function buildPluginMenu ($plugin) {
        $menuFile = base_path($plugin) . 'menu.php';
        $menuTree = require($menuFile);
        $menu     = MenuService::buildMenuList($menuTree);
        Permission::where('plugin', $plugin)->delete();
        foreach ($menu as $v) {
            $v['plugin'] = $plugin;
            Permission::create($v);
        }
    }

    /**
     * 发布插件静态文件
     */
    public static function publishPluginFile ($plugin) {
        $pluginDir = base_path($plugin) . 'admin/src/app/' . $plugin;
        if (is_dir($pluginDir)) {
            $pluginAdminView = root_path('admin/src/app/' . $plugin);
            // 发布插件静态文件到admin/src/app
            if (is_dir($pluginAdminView)) {
                // 备份
                copyDirectory($pluginAdminView, runtime_path('backups/admin/admin/' . time() . '/src/app') . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR);
                // 删除
                // delete_directory($pluginAdminView);
            }
            // 复制
            copyDirectory($pluginDir, $pluginAdminView);
        }
    }
}