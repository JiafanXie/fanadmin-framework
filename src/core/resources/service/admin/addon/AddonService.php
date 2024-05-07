<?php

namespace core\src\http\service\admin\addon;


use core\src\http\model\addon\AddonModel as Addon;
use think\db\exception\DbException;
use Throwable;

/**
 * 插件应用服务层
 * Class AddonService
 * @package core\lib\addon
 */
class AddonService extends BaseAddonService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Addon();
    }

    /**
     * 获取已下载的插件
     * @return array
     */
    public function getLocalAddonList()
    {
        $list = [];
        $online_app_list = [];
        $install_addon_list = $this->model->append(['status_name'])->column('name, icon, key, desc, status, author, version, install_time, update_time, cover', 'key');
//        try {
//            $niucloud_module_list = (new CoreModuleService())->getModuleList()['data'] ?? [];
//            foreach ($niucloud_module_list as $v) {
//                $data = array(
//                    'title' => $v['app']['app_name'],
//                    'desc' => $v['app']['app_desc'],
//                    'key' => $v['app']['app_key'] ?? '',
//                    'version' => $v['version'] ?? '',
//                    'author' => $v['app']['app_name'],
//                    'type' => $v['app']['app_type'],
//                    'support_app' => $v['app']['support_channel'] ?? [],
//                    'is_download' => false,
//                    'is_local' => false,
//                    'icon' => $v['app']['app_logo'],
//                    'cover' => $v['app']['window_logo'][0],
//                );
//                $data['install_info'] = $install_addon_list[$v['app']['app_key']] ?? [];
//                $list[$v['app']['app_key']] = $data;
//            }
//            $online_app_list = array_column($list, 'key');
//        } catch ( Throwable $e ) {
//            $error = $e->getMessage();
//        }
        $files = getFilesByDir($this->addonPath);
        if (!empty($files)) {
            foreach ($files as $path) {
                $data = $this->getAddonConfig($path);
                if (isset($data['key'])) {
                    $data['icon']         = is_file($data['icon']) ? imageToBase64($data['icon']) : '';
                    $data['cover']        = is_file($data['cover']) ? imageToBase64($data['cover']) : '';
                    $key                  = $data['key'];
                    $data['install_info'] = $install_addon_list[$key] ?? [];
                    $data['is_download']  = true;
                    $data['is_local']     = in_array($data['key'], $online_app_list) ? false : true;
                    $data['version']      = isset($list[ $data['key'] ]) ? $list[ $data['key'] ]['version'] : $data['version'];
                    $list[$key]           = $data;
                }
            }
        }
        return ['list' => $list, 'error' => $error ?? ''];
    }

    /**
     * 安装插件检测安装环境
     * @param string $addon
     */
    public function installCheck(string $addon)
    {
        return ( new AddonInstallService($addon) )->installCheck();
    }

    /**
     * 已下载的插件数量
     * @return int
     */
    public function getLocalAddonCount()
    {
        $files = getFilesByDir($this->addonPath);
        return count($files);
    }

    /**
     * 获取已安装插件数量
     * @param array $where
     * @return int
     * @throws DbException
     */
    public function getCount(array $where = [])
    {
        return $this->model->where($where)->count();
    }

    /**
     * 安装的插件分页
     * @param array $where
     * @return array
     * @throws DbException
     */
    public function getList(array $where)
    {
        $field = 'id, 
            title, 
            key, 
            desc, 
            version, 
            status, 
            icon, 
            create_time, 
            install_time';
        $searchModel = $this->model->where([])->withSearch(['title'], $where)->field($field)->order('id desc');
        return $this->pageQuery($searchModel);
    }

    /**
     * 插件详情
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        return $this->model->where([['id', '=', $id]])->findOrEmpty()->toArray();
    }

    /**
     * 设置插件(安装或更新)
     * @param array $params
     * @return bool
     */
    public function set(array $params)
    {
        $title = $params['title'];
        $key   = $params['key'];
        $addon = $this->model->where([
            ['key', '=', $key],
        ])->findOrEmpty();
        $version = $params['version'];//版本号
        $desc    = $params['desc'];
        $icon    = $params['icon'];
        $data    = [
            'title'       => $title,
            'version'     => $version,
            'status'      => 1,
            'desc'        => $desc,
            'icon'        => $icon,
            'key'         => $key,
            'compile'     => $params['compile'] ?? [],
            'type'        => $params['type'],
            'support_app' => $params['support_app'] ?? ''
        ];
        if ($addon->isEmpty()) {
            $data['install_time'] = time();
            $this->model->create($data);
        } else {
            $data['update_time'] = time();
            $addon->save($data);
        }
        return true;
    }

    /**
     * 通过key查询插件
     * @param string $key
     * @return array
     */
    public function getInfoByKey(string $key)
    {
        return $this->model->where([['key', '=', $key]])->findOrEmpty()->toArray();
    }

    /**
     * 通过插件名删除插件
     * @param string $key
     * @return bool
     */
    public function deleteByKey(string $key)
    {
        $this->model->where([['key', '=', $key]])->delete();
        return true;
    }

    /**
     * 修改插件状态
     * @param int $id
     * @param int $status
     * @return bool
     */
    public function status(int $id, int $status)
    {
        $this->model->where([['id', '=', $id]])->update(['status' => $status]);
        return true;
    }

    /**
     * @return mixed
     */
    public function getAppList()
    {
        return event('addon', []);
    }

    /**
     * 查询已安装的有效的应用
     * @return array
     */
    public function getInstallAddonList(){
        return $this->model->where([['status', '=', 1]])->append(['status_name'])->column('title, icon, key, desc, status, type, support_app', 'key');
    }

    /**
     * 应用key缓存
     * @param $keys
     * @return mixed|string
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAddonListByKeys($keys){
        sort($keys);
        $cache_name = 'addon_list'.implode('_', $keys);
        return cacheRemember(
            $cache_name,
            function () use ($keys) {
                $where = [
                    ['key', 'in', $keys],
                    ['status', '=', 1]
                ];
                return $this->model->where($where)->field('title, icon, key, desc, status, cover')->select()->toArray();
            },
            self::$cacheTagName
        );
    }
}
