<?php

namespace FanAdmin\src\http\service\admin\addon;

use app\dict\addon\AddonDict;
use app\job\sys\AddonInstall;
use app\model\site\SiteModel as Site;
use app\service\admin\sys\MenuService;
use app\service\admin\sys\SystemService;
use FanAdmin\lib\addon\trait\WapTrait;
use FanAdmin\src\http\service\admin\addon\AddonCloudService;
use app\service\FanAdmin\menu\CoreMenuService;
use app\service\FanAdmin\schedule\CoreScheduleInstallService;
use FanAdmin\exception\AddonException;
use FanAdmin\exception\CommonException;
use FanAdmin\lib\Terminal\Terminal;
use think\db\exception\DbException;
use think\db\exception\PDOException;
use think\facade\Cache;
use think\facade\Db;
use think\Response;

/**
 * 安装服务层
 * Class AddonInstallService
 * @package FanAdmin\lib\addon
 */
class AddonInstallService extends BaseAddonService
{
    /**
     *
     */
    use WapTrait;

    /**
     * @var
     */
    public static $instance;

    /**
     * @var array[] 需要迁移的文件，用于检测是否冲突
     */
    public $installFiles = [
        'admin' => [],
        'web'   => [],
        'wap'   => [],
    ];

    /**
     * @var array[]
     */
    private $files = [
        'server'     => [],
        'admin' => [],
        'web'        => [],
        'wap'        => [],
        'resource'   => []
    ];

    /**
     * @var string[]
     */
    private $flowPath = [
        'file',
        'sql',
        'menu',
        'diy'
    ];

    /**
     * @var
     */
    private $addon;

    /**
     * @var string
     */
    private $installAddonPath;

    /**
     * @var string
     */
    private $cacheKey = '';

    /**
     * @var mixed
     */
    private $installTask = null;

    public function __construct($addon)
    {
        parent::__construct();
        $this->addon = $addon;
        $this->installAddonPath = $this->addonPath . $addon . DIRECTORY_SEPARATOR;
        $this->cacheKey = "install_{$addon}";
        $this->installTask = Cache::get('install_task');
    }

    /**
     * 初始化实例
     * @param string $addon
     * @return static
     */
    public static function instance(string $addon)
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($addon);
        }
        return self::$instance;
    }

    /**
     * 安装前检测
     * @return array
     */
    public function installCheck()
    {
        $fromAdminDir    = $this->installAddonPath . 'admin' . DIRECTORY_SEPARATOR;
        $fromWebDir      = $this->installAddonPath . 'web' . DIRECTORY_SEPARATOR;
        $fromWapDir      = $this->installAddonPath . 'uni-app' . DIRECTORY_SEPARATOR;
        $fromResourceDir = $this->installAddonPath . 'resource' . DIRECTORY_SEPARATOR;

        // 放入的文件
        $toAdminDir    = $this->rootPath . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon'. DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toWebDir      = $this->rootPath . 'web' . DIRECTORY_SEPARATOR;
        $toWapDir      = $this->rootPath . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon'. DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toResourceDir = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;

        try {
            if (!is_dir($this->rootPath . 'admin' . DIRECTORY_SEPARATOR)) throw new CommonException('ADMIN_DIR_NOT_EXIST');
            if (!is_dir($this->rootPath . 'web' . DIRECTORY_SEPARATOR)) throw new CommonException('WEB_DIR_NOT_EXIST');
            if (!is_dir($this->rootPath . 'uni-app' . DIRECTORY_SEPARATOR)) throw new CommonException('UNIAPP_DIR_NOT_EXIST');
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'open basedir') !== false) {
                throw new CommonException('OPEN_BASEDIR_ERROR');
            }
            throw new CommonException($e->getMessage());
        }

        // 配置文件
        $packagePath = $this->installAddonPath . 'package' . DIRECTORY_SEPARATOR;
        $packageFile = [];
        searchDir($packagePath, $packageFile);
        $packageFile = array_map(function ($file) use ($packagePath) {
            return str_replace($packagePath . DIRECTORY_SEPARATOR, '', $file);
        }, $packageFile);
        $data = [
            // 目录检测
            'dir' => [
                // 要求可读权限
                'is_readable' => [],
                // 要求可写权限
                'is_write'    => []
            ]
        ];

        if (is_dir($fromAdminDir)) $data['dir']['is_readable'][] = ['dir' => str_replace(projectPath(), '', $fromAdminDir), 'status' => is_readable($fromAdminDir)];
        if (is_dir($fromWebDir)) $data['dir']['is_readable'][] = ['dir' => str_replace(projectPath(), '', $fromWebDir), 'status' => is_readable($fromWebDir)];
        if (is_dir($fromWapDir)) $data['dir']['is_readable'][] = ['dir' => str_replace(projectPath(), '', $fromWapDir), 'status' => is_readable($fromWapDir)];
        if (is_dir($fromResourceDir)) $data['dir']['is_readable'][] = ['dir' => str_replace(projectPath(), '', $fromResourceDir), 'status' => is_readable($fromResourceDir)];

        $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toAdminDir), 'status' => is_dir($toAdminDir) ? is_write($toAdminDir) : mkdir($toAdminDir, 0777, true)];
        $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toWebDir), 'status' => is_dir($toWebDir) ? is_write($toWebDir) : mkdir($toWebDir, 0777, true)];
        $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toWapDir), 'status' => is_dir($toWapDir) ? is_write($toWapDir) : mkdir($toWapDir, 0777, true)];
        $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toResourceDir), 'status' => is_dir($toResourceDir) ? is_write($toResourceDir) : mkdir($toResourceDir, 0777, true)];
        $checkRes = array_merge(
            array_column($data['dir']['is_readable'], 'status'),
            array_column($data['dir']['is_write'], 'status')
        );
        // 是否通过校验
        $data['is_pass'] = !in_array(false, $checkRes);
        Cache::set($this->cacheKey . '_install_check', $data['is_pass']);
        return $data;
    }

    /**
     * 插件安装
     * @param string $mode
     * @return array|bool
     */
    public function install(string $mode = 'local')
    {
        $addonService = new AddonService();
        if (!empty($addonService->getInfoByKey($this->addon))) throw new AddonException('REPEAT_INSTALL');

        $installData = $this->getAddonConfig($this->addon);
        if (empty($installData)) throw new AddonException('ADDON_INFO_FILE_NOT_EXIST');

        $checkRes = Cache::get($this->cacheKey . '_install_check');
        if (!$checkRes) throw new CommonException('INSTALL_CHECK_NOT_PASS');

        if ($this->installTask) throw new CommonException('ADDON_INSTALLING');
        $this->installTask = [ 'mode' => $mode, 'addon' => $this->addon, 'step' => [], 'timestamp' => time() ];
        Cache::set('install_task', $this->installTask);

        set_time_limit(0);

        $installStep = ['installDir','installWap','installDepend'];

        if (!empty($installData['compile']) || $mode == 'cloud') {
            // 备份前端目录
            $installStep[] = 'backupFrontend';
        }

        // 检测插件是否存在编译内容
        if (!empty($installData['compile'])) {
            $installStep[] = 'coverCompile';
        }

        if ($mode == 'cloud') {
            $installStep[] = 'cloudInstall';
        } else {
            $installStep[] = 'handleAddonInstall';
        }

        try {
            foreach ($installStep as $step) {
                $this->installTask['step'][] = $step;
                $this->$step();
                if ($step != 'handleAddonInstall') Cache::set('install_task', $this->installTask);
            }

            if ($mode != 'cloud') {
                // 配置文件
                $packagePath = $this->installAddonPath . 'package' . DIRECTORY_SEPARATOR;
                $packageFile = [];
                searchDir($packagePath, $packageFile);
                $packageFile = array_map(function ($file) use ($packagePath) {
                    return str_replace($packagePath . DIRECTORY_SEPARATOR, '', $file);
                }, $packageFile);

                $tips = [getLang('dict_addon.install_after_update')];
                if (in_array('admin-package.json', $packageFile)) $tips[] = getLang('dict_addon.install_after_admin_update');
                if (in_array('composer.json', $packageFile)) $tips[] = getLang('dict_addon.install_after_composer_update');
                if (in_array('uni-app-package.json', $packageFile)) $tips[] = getLang('dict_addon.install_after_wap_update');
                if (in_array('web-package.json', $packageFile)) $tips[] = getLang('dict_addon.install_after_web_update');
                return $tips;
            }
            return true;
        } catch (\Exception $e) {
            Cache::set('install_task', $this->installTask);
            $this->installExceptionHandle();
            if (strpos($e->getMessage(), 'open basedir') !== false) {
                throw new CommonException('OPEN_BASEDIR_ERROR');
            }
            throw new CommonException($e->getMessage());
        }
    }

    /**
     * 安装异常处理
     */
    public function installExceptionHandle() {
        $installTask = Cache::get('install_task');
        if (in_array('installDir', $installTask['step'])) {
            @$this->uninstallDir();
        }
        if (in_array('installWap', $installTask['step'])) {
            @$this->uninstallWap();
        }
        if (in_array('backupFrontend', $installTask['step'])) {
            @$this->revertFrontendBackup();
        }
        Cache::set('install_task', null);
    }

    /**
     * 取消安装任务
     */
    public function cancleInstall() {
        if (Cache::get('install_task')) $this->installExceptionHandle();
    }

    /**
     * 获取安装任务
     * @return mixed
     */
    public function getInstallTask() {
        return $this->installTask;
    }

    /**
     * 安装迁移复制文件
     * @return bool
     */
    public function installDir()
    {
        $fromAdminDir    = $this->installAddonPath . 'admin' . DIRECTORY_SEPARATOR;
        $fromWebDir      = $this->installAddonPath . 'web' . DIRECTORY_SEPARATOR;
        $fromWapDir      = $this->installAddonPath . 'uni-app' . DIRECTORY_SEPARATOR;
        $fromResourceDir = $this->installAddonPath . 'resource' . DIRECTORY_SEPARATOR;

        // 放入的文件
        $toAdminDir     = $this->rootPath . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toWebDir       = $this->rootPath . 'web' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toWapDir       = $this->rootPath . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toResourceDir  = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;

        // 安装admin管理端
        if (file_exists($fromAdminDir)) {
            dirCopy($fromAdminDir, $toAdminDir, $this->files['admin'], exclude_dirs:['icon']);
            // 判断图标目录是否存在
            if (is_dir($fromAdminDir . 'icon')) {
                $addonIconDir = $this->rootPath . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'icon' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon;
                dirCopy($fromAdminDir . 'icon', $addonIconDir);
            }
            // 编译后台图标库文件
            $this->compileAdminIcon();
        }

        // 安装电脑端
        if (file_exists($fromWebDir)) {
            // 安装布局文件
            $layout = $fromWebDir . 'layouts';
            if (is_dir($layout)) {
                dirCopy($layout, $this->rootPath . 'web' . DIRECTORY_SEPARATOR . 'layouts');
                delTargetDir($layout, true);
            }
            dirCopy($fromWebDir, $toWebDir, $this->files['web']);
        }

        // 安装手机端
        if (file_exists($fromWapDir)) {
            dirCopy($fromWapDir, $toWapDir, $this->files['wap']);
        }

        //安装资源文件
        if (file_exists($fromResourceDir)) {
            dirCopy($fromResourceDir, $toResourceDir, $this->files['resource']);
        }

        return true;
    }

    /**
     * 编译后台图标库文件
     * 图标开发注意事项，不能占用  iconfont、icon 关键词（会跟系统图标冲突），建议增加业务前缀，比如 旅游业：recharge
     * @return bool
     */
    public function compileAdminIcon()
    {
        $compilePath = $this->rootPath . str_replace('/', DIRECTORY_SEPARATOR, 'admin/src/styles/icon/');

        $content = "";
        $rootPath = $compilePath . 'addon'; // 插件图标根目录
        $fileArr = getFileMap($rootPath);
        if (!empty($fileArr)) {
            foreach ($fileArr as $ck => $cv) {
                if (str_contains($cv, '.css')) {
                    $path     = str_replace($rootPath . '/', '', $ck);
                    $path     = str_replace('/.css', '', $path);
                    $content .= "@import \"addon/{$path}\";\n";
                }
            }
        }
        file_put_contents($compilePath . 'addon-iconfont.css', $content);
        return true;
    }

    /**
     * 安装sql
     * @return bool
     */
    public function installSql()
    {
        $sql = $this->installAddonPath . 'sql' . DIRECTORY_SEPARATOR . 'install.sql';
        $this->executeSql($sql);
        return true;
    }

    /**
     * 执行sql
     * @param string $sql_file
     * @return bool
     */
    public static function executeSql(string $sql_file): bool
    {
        if (is_file($sql_file)) {
            $sql = file_get_contents($sql_file);
            // 执行sql
            $sqlArr = parseSql($sql);
            if (!empty($sqlArr)) {
                $prefix = config('database.connections.mysql.prefix');
                Db::startTrans();
                try {
                    foreach ($sqlArr as $sqlLine) {
                        $sqlLine = trim($sqlLine);
                        if (!empty($sqlLine)) {
                            $sqlLine = str_ireplace('{{prefix}}', $prefix, $sqlLine);
                            $sqlLine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $sqlLine);
                            Db::execute($sqlLine);
                        }
                    }
                    Db::commit();
                    return true;
                } catch ( PDOException $e ) {
                    Db::rollback();
                    throw new AddonException($e->getMessage());
                }
            }
        }
        return true;
    }

    /**
     * 执行插件install方法
     * @return bool
     */
    public function handleAddonInstall()
    {
        // 执行安装sql
        $this->installSql();
        // 安装菜单
        $this->installMenu();
        // 安装计划任务
        $this->installSchedule();

        $addonService = new AddonService();
        $installData = $this->getAddonConfig($this->addon);
        $installData['icon'] = 'addon/' . $this->addon . '/icon.png';
        $addonService->set($installData);
        //清理缓存
        Cache::tag(self::$cacheTagName)->clear();
        //执行命令
        //执行插件安装方法
        $class = "addon\\" . $this->addon . "\\" . 'Addon';
        if (class_exists($class)) {
            (new $class())->install();
        }
        // 清除插件安装中标识
        Cache::delete('install_task');
        Cache::delete($this->cacheKey . '_install_check');
        return true;
    }

    /**
     * 合并依赖
     */
    public function installDepend()
    {
        (new AddonDependService())->installDepend($this->addon);
    }

    /**
     * 备份前端页面
     */
    public function backupFrontend() {
        $backupDir = runtime_path() . 'backup' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
        if (is_dir($backupDir)) delTargetDir($backupDir, true);

        foreach (['admin', 'wap', 'web'] as $port) {
            $toDir = public_path() . $port;
            if (is_dir($toDir)) {
                if (is_dir($backupDir . $port)) delTargetDir($backupDir . $port, true);
                // 备份原目录
                dirCopy($toDir, $backupDir . $port);
            }
        }
    }

    /**
     * 还原被覆盖前的文件
     */
    public function revertFrontendBackup() {
        $backupDir = runtime_path() . 'backup' . DIRECTORY_SEPARATOR . 'frontend' . DIRECTORY_SEPARATOR;
        $backupFile = [];

        searchDir($backupDir, $backupFile);

        if (!empty($backupFile)) {
            dirCopy(public_path(), $backupDir);
            @delTargetDir($backupDir, true);
        }
    }

    /**
     * 插件编译文件覆盖
     */
    public function coverCompile() {
        $compile = $this->getAddonConfig($this->addon)['compile'];
        foreach ($compile as $port) {
            $toDir = public_path() . $port;
            $fromDir = $this->addonPath . 'compile' . DIRECTORY_SEPARATOR . $port;

            if (is_dir($fromDir) && is_dir($toDir)) {
                // 删除后覆盖目录
                delTargetDir($toDir, true);
                dirCopy($fromDir, $toDir . $port);
            }
        }
    }

    /**
     * 云安装
     */
    public function cloudInstall() {
        (new AddonCloudService())->cloudBuild($this->addon);
    }

    /**
     * 插件卸载环境检测
     * @param string $addon
     * @return void
     */
    public function uninstallCheck() {
        $data = [
            // 目录检测
            'dir' => [
                // 要求可读权限
                'is_readable' => [],
                // 要求可写权限
                'is_write'    => []
            ]
        ];

        // 将要删除的根目录
        $toAdminDir    = $this->rootPath . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toWebDir      = $this->rootPath . 'web' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toWapDir      = $this->rootPath . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $toResourceDir = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;

        if (is_dir($toAdminDir)) $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toAdminDir), 'status' => is_write($toAdminDir)];
        if (is_dir($toWebDir)) $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toWebDir), 'status' => is_write($toWebDir)];
        if (is_dir($toWapDir)) $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toWapDir), 'status' => is_write($toWapDir)];
        if (is_dir($toResourceDir)) $data['dir']['is_write'][] = ['dir' => str_replace(projectPath(), '', $toResourceDir), 'status' => is_write($toResourceDir)];

        $checkRes = array_merge(
            array_column($data['dir']['is_readable'], 'status'),
            array_column($data['dir']['is_write'], 'status')
        );

        // 是否通过校验
        $data['is_pass'] = !in_array(false, $checkRes);
        return $data;
    }

    /**
     * 卸载插件
     * @return bool
     * @throws DbException
     */
    public function uninstall()
    {
        $site_num = (new Site())->where([ ['app', '=', $this->addon] ])->count('site_id');
        if ($site_num) throw new CommonException('APP_NOT_ALLOW_UNINSTALL');

        //执行插件卸载方法
        $class = "addon\\" . $this->addon . "\\" . 'Addon';
        if (class_exists($class)) {
            (new $class())->uninstall();
        }
        $addonService = new AddonService();
        $addon_info   = $addonService->getInfoByKey($this->addon);
        if (empty($addon_info)) throw new AddonException('NOT_UNINSTALL');
        if (!$this->uninstallSql()) throw new AddonException('ADDON_SQL_FAIL');
        if (!$this->uninstallDir()) throw new AddonException('ADDON_DIR_FAIL');

        // 卸载菜单
        $this->uninstallMenu();

        // 卸载计划任务
        $this->uninstallSchedule();

        // 卸载wap
        $this->uninstallWap();

        // 还原备份
        if (!empty($addon_info['compile'])) (new CoreAddonCompileHandleService())->revertBackup();

        $addonService = new AddonService();
        $addonService->deleteByKey($this->addon);

        //清理缓存
        Cache::tag(self::$cacheTagName)->clear();
        return true;
    }

    /**
     * 卸载数据库
     * @return true
     */
    public function uninstallSql()
    {
        $sql = $this->install_addon_path . 'sql' . DIRECTORY_SEPARATOR . 'uninstall.sql';
        $this->executeSql($sql);
        return true;
    }

    /**
     * 卸载插件
     * @return true
     */
    public function uninstallDir()
    {
        // 将要删除的根目录
        $to_admin_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $to_web_dir = $this->root_path . 'web' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $to_web_layouts = $this->root_path . 'web' . DIRECTORY_SEPARATOR . 'layouts' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $to_wap_dir = $this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;
        $to_resource_dir = public_path() . 'addon' . DIRECTORY_SEPARATOR . $this->addon . DIRECTORY_SEPARATOR;

        // 卸载admin管理端
        if (is_dir($to_admin_dir)) del_target_dir($to_admin_dir, true);
        // 移除admin图标
        $addon_icon_dir = $this->root_path . 'admin' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'icon' . DIRECTORY_SEPARATOR . 'addon' . DIRECTORY_SEPARATOR . $this->addon;
        if (is_dir($addon_icon_dir)) del_target_dir($addon_icon_dir, true);

        // 编译后台图标库文件
        $this->compileAdminIcon();

        // 卸载pc端
        if (is_dir($to_web_dir)) del_target_dir($to_web_dir, true);
        if (is_dir($to_web_layouts)) del_target_dir($to_web_layouts, true);

        // 卸载手机端
        if (is_dir($to_wap_dir)) del_target_dir($to_wap_dir, true);

        //删除资源文件
        if (is_dir($to_resource_dir)) del_target_dir($to_resource_dir, true);

        //todo  卸载插件目录涉及到的空文件
        return true;
    }

    /**
     * 卸载菜单
     * @return true
     * @throws DbException
     */
    public function uninstallMenu()
    {
        $core_menu_service = new CoreMenuService();
        $core_menu_service->deleteByAddon($this->addon);
        Cache::tag(MenuService::$cache_tag_name)->clear();
        return true;
    }

    /**
     * 卸载计划任务
     * @return true
     */
    public function uninstallSchedule()
    {
        (new CoreScheduleInstallService())->uninstallAddonSchedule($this->addon);
        return true;
    }

    /**
     * 卸载手机端
     * @return void
     */
    public function uninstallWap()
    {
        // 编译 diy-group 自定义组件代码文件
        $this->compileDiyComponentsCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

        // 编译 fixed-group 固定模板组件代码文件
        $this->compileFixedComponentsCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

        // 编译 pages.json 页面路由代码文件
        $this->uninstallPageCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);

        // 编译 加载插件标题语言包
        $this->compileLocale($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

    }

    /**
     * 安装插件菜单
     * @return true
     */
    public function installMenu()
    {
        (new CoreMenuService)->refreshAddonMenu($this->addon);
        Cache::tag(MenuService::$cache_tag_name)->clear();
        return true;
    }

    /**
     * 安装手机端
     * @return void
     */
    public function installWap()
    {

        // 编译 diy-group 自定义组件代码文件
        $this->compileDiyComponentsCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

        // 编译 fixed-group 固定模板组件代码文件
        $this->compileFixedComponentsCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

        // 编译 pages.json 页面路由代码文件
        $this->installPageCode($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR);

        // 编译 加载插件标题语言包
        $this->compileLocale($this->root_path . 'uni-app' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR, $this->addon);

    }

    public function download()
    {

    }

    public function edit()
    {

    }

    /**
     * 更新composer依赖
     * @return true
     */
    public function updateComposer()
    {
        $result = Terminal::execute(root_path(), 'composer update');
        if ($result !== true) {
            throw new CommonException($result);
        }
        return $result;
    }

    /**
     * 更新admin端依赖
     * @return true
     */
    public function updateAdminDependencies()
    {
        $result = Terminal::execute(root_path() . '../admin/', 'npm install');
        if ($result !== true) {
            throw new CommonException($result);
        }
        return $result;
    }

    /**
     * 更新手机端依赖
     * @return true
     */
    public function updateWapDependencies()
    {
        $result = Terminal::execute(root_path() . '../uni-app/', 'npm install');
        if ($result !== true) {
            throw new CommonException($result);
        }
        return $result;
    }

    /**
     * 更新web端依赖
     * @return true
     */
    public function updateWebDependencies()
    {
        $result = Terminal::execute(root_path() . '../web/', 'npm install');
        if ($result !== true) {
            throw new CommonException($result);
        }
        return $result;
    }

    /**
     * 安装完成 销毁插件实例
     * @return true
     */
    public function installComplete()
    {
        return true;
    }

    /**
     * 安装计划任务
     * @return true
     */
    public function installSchedule()
    {
        (new CoreScheduleInstallService())->installAddonSchedule($this->addon);
        return true;
    }

    /**
     * 处理编译之后的文件
     * @return void
     */
    public function handleBuildFile() {
        return true;
    }
}
