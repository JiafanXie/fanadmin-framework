<?php

namespace core\lib\addon\service;

use app\dict\sys\MenuDict;
use app\model\sys\SysMenu;
use app\service\admin\sys\MenuService;
use core\base\BaseCoreService;
use core\base\service\BaseService as Base;
use core\exception\AddonException;

/**
 * 插件开发服务层
 */
class AddonDevelopBuildService extends Base
{
    protected $root_path;

    protected $addon_path;

    protected $addon;
    protected $build_path;

    public function __construct()
    {
        parent::__construct();
        $this->root_path = projectPath();
    }

    /**
     * 插件打包
     * @param string $addon
     * @return void
     */
    public function build(string $addon)
    {
        $this->addon = $addon;
        $this->addon_path = root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;

        if (!is_dir($this->addon_path)) throw new AddonException('ADDON_IS_NOT_EXIST');//当前目录中不存在此项插件

        $this->admin();
        $this->uniapp();
        $this->buildUniappPagesJson();
        $this->buildUniappLangJson();
        $this->web();
        $this->resource();
        $this->menu('admin');
        $this->menu('site');

        $zip_file = runtime_path() . $addon . '.zip';
        if (file_exists($zip_file)) unlink($zip_file);
        (new AddonDevelopDownloadService(''))->compressToZip($this->addon_path, $zip_file);

        return true;
    }

    /**
     * 下载
     * @param string $addon
     * @return \think\response\File
     */
    public function download(string $addon) {
        $zip_file = runtime_path() . $addon . '.zip';
        if (!file_exists($zip_file)) throw new AddonException('ADDON_ZIP_ERROR');//下载失败
        return str_replace(projectPath(), '', $zip_file);
    }

    /**
     * 同步菜单
     * @param string $app_type
     * @return true
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function menu(string $app_type) {
        $where = [ ['app_type', '=', $app_type], ['addon', '=', $this->addon] ];
        $field = 'menu_name,menu_key,menu_short_name,parent_key,menu_type,icon,api_url,router_path,view_path,methods,sort,status,is_show';
        $menu = (new SysMenu())->where($where)->field($field)->order('sort', 'desc')->select()->toArray();
        if (!empty($menu)) {
            $menu = (new MenuService())->menuToTree($menu, 'menu_key', 'parent_key', 'children');
            (new SysMenu())->where($where)->update(['source' => MenuDict::SYSTEM]);
        }

        $addon_dict = $this->addon_path . 'app' . DIRECTORY_SEPARATOR . 'dict' . DIRECTORY_SEPARATOR . 'menu' . DIRECTORY_SEPARATOR . $app_type . '.php';

        $content = '<?php' . PHP_EOL;
        $content .= 'return [' . PHP_EOL;
        $content .= $this->arrayFormat($menu);
        $content .= '];';
        file_put_contents($addon_dict, $content);

        return true;
    }

    private function arrayFormat($array, $level = 1) {
        $tab = '';
        for ($i = 0; $i < $level; $i++) {
            $tab .= '    ';
        }
        $content = '';
        foreach ($array as $k => $v) {
            if (in_array($k, ['status_name', 'menu_type_name']) || ($level > 2 && $k == 'parent_key')) continue;
            if (is_array($v)) {
                $content .= $tab;
                if (is_string($k)) {
                    $content .= "'{$k}' => ";
                }
                $content .= '[' . PHP_EOL . $this->arrayFormat($v, $level + 1);
                $content .= $tab . '],' . PHP_EOL;
            } else {
                $content .= $tab ."'{$k}' => '{$v}'," . PHP_EOL;
            }
        }
        return $content;
    }

    /**
     * admin打包
     * @return void
     */
    public function admin()
    {
        $admin_path = $this->root_path . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($admin_path)) return true;

        $addon_admin_path = $this->addon_path . 'admin' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_admin_path)) delTargetDir($addon_admin_path, true);
        dirCopy($admin_path, $addon_admin_path);

        // 打包admin icon文件
        $icon_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'icon' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon;
        if (is_dir($icon_dir)) dirCopy($icon_dir, $addon_admin_path . 'icon');

        return true;
    }

    /**
     * wap打包
     * @return void
     */
    public function uniapp()
    {
        $uniapp_path = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($uniapp_path)) return true;

        $addon_uniapp_path = $this->addon_path . 'uni-app' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_uniapp_path)) delTargetDir($addon_uniapp_path, true);
        dirCopy($uniapp_path, $addon_uniapp_path);

        return true;
    }

    public function buildUniappPagesJson() {
        $pages_json = file_get_contents($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'pages.json');
        $code_begin = strtoupper($this->addon) . '_PAGE_BEGIN' . PHP_EOL;
        $code_end = strtoupper($this->addon) . '_PAGE_END' . PHP_EOL;

        if(strpos($pages_json, $code_begin) !== false && strpos($pages_json, $code_end) !== false)
        {
            $pattern = "/\/\/\s+{$code_begin}([\S\s]+)\/\/\s+{$code_end}?/";
            preg_match($pattern, $pages_json, $match);

            if (!empty($match)) {
                $addon_pages = str_replace(PHP_EOL.','.PHP_EOL, '', $match[1]);

                $content = '<?php' . PHP_EOL;
                $content .= 'return [' . PHP_EOL . "    'pages' => <<<EOT" . PHP_EOL . '        // PAGE_BEGIN' . PHP_EOL;
                $content .= $addon_pages;
                $content .= '// PAGE_END' . PHP_EOL . 'EOT' . PHP_EOL . '];';

                if (!is_dir($this->addon_path . 'package')) dirMkdir($this->addon_path . 'package');
                file_put_contents($this->addon_path . 'package' . DIRECTORY_SEPARATOR . 'uni-app-pages.php', $content);
            }
        }
        return true;
    }

    public function buildUniappLangJson() {
        $zh_json = json_decode(file_get_contents($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . 'zh-Hans.json'), true);
        $en_json = json_decode(file_get_contents($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR . 'en.json'), true);

        $zh = [];
        $en = [];
        foreach ($zh_json as $key => $value) {
            if (strpos($key, $this->addon . '.') === 0) {
                $key = str_replace($this->addon . '.', '', $key);
                $zh[$key] = $value;
            }
        }
        foreach ($en_json as $key => $value) {
            if (strpos($key, $this->addon . '.') === 0) {
                $key = str_replace($this->addon . '.', '', $key);
                $en[$key] = $value;
            }
        }
        $addon_lang_dir = $this->addon_path . 'uni-app' . DIRECTORY_SEPARATOR . 'locale' . DIRECTORY_SEPARATOR;
        if (!is_dir($addon_lang_dir)) dirMkdir($addon_lang_dir);
        (new BaseAddonService())->writeArrayToJsonFile($zh, $addon_lang_dir . 'zh-Hans.json');
        (new BaseAddonService())->writeArrayToJsonFile($en, $addon_lang_dir . 'en.json');

        return true;
    }

    /**
     * web打包
     * @return void
     */
    public function web()
    {
        $web_path = $this->root_path . 'web' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($web_path)) return true;

        $addon_web_path = $this->addon_path . 'web' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_web_path)) delTargetDir($addon_web_path, true);
        dirCopy($web_path, $addon_web_path);

        $layout = $this->root_path . 'web' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->addon;
        if (is_dir($layout)) {
            $layout_dir = $addon_web_path . 'layouts' . DIRECTORY_SEPARATOR . $this->addon;
            if (is_dir($layout_dir)) delTargetDir($layout_dir, true);
            else dirMkdir($layout_dir);
            dirCopy($layout, $layout_dir);
        }

        return true;
    }

    /**
     * 打包资源文件
     * @return true
     */
    public function resource() {
        $resource_path = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        if (!is_dir($resource_path)) return true;

        $addon_resource_path = $this->addon_path . 'resource' . DIRECTORY_SEPARATOR;
        if (is_dir($addon_resource_path)) delTargetDir($addon_resource_path, true);
        dirCopy($resource_path, $addon_resource_path);

        return true;
    }
}
