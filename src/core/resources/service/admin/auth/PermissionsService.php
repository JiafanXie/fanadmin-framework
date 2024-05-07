<?php

namespace core\src\http\service\admin\auth;


use app\dict\sys\MenuDict;
use app\model\sys\SysMenu;
use core\base\service\BaseService as Base;
use core\exception\AdminException;
use core\src\http\model\auth\PermissionModel as Permission;
use think\db\exception\DbException;
use think\facade\Cache;
use think\Model;

/**
 * 权限服务层
 * Class PermissionsService
 * @package core\src\addon\auth\src\service\auth
 */
class PermissionsService extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Permission();
    }

    /**
     * 获取菜单列表
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList()
    {
        $menu =  $this->getPermissionsList();
        $list = $this->buildMenuTree($menu);
        return $list;
    }

    /**
     * 菜单详情
     * @param int $id (管理员id)
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = '*';
        $info = $this->model->field($field)->where([['id', "=", $id]])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 添加菜单
     * @param array $data
     * @return Permission|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function add(array $data)
    {
        $menu = $this->model->find($data['key'], $data['app_type']);
        if(!$menu->isEmpty()) throw new AdminException('创建失败');
        $data['source'] = 'system';
        $res = $this->model->create($data);
        return $res;
    }

    /**
     * 编辑菜单
     * @param int $id
     * @param array $data
     * @return Permission
     */
    public function edit(int $id, array $data)
    {
        $res = $this->model->where([
            ['id', '=', $id]
        ])->update($data);
        return $res;
    }

    /**
     * 菜单删除
     * @param string $ids
     * @return mixed
     * @throws DbException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function delete(string $ids){
        $ids = explode(',', $ids);
        $where = [
            ['id', 'in', $ids]
        ];
        $menu = $this->model->where($where)->select()->toArray();
        if(!$menu){
            throw new AdminException('没有数据');
        }
        $res = $this->model->where($where)->delete();
        return  $res;
    }

    /**
     * 获取菜单列表
     * @param array $where
     * @return Permission[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionsList(array $where= [])
    {
        $menu =  $this->model->where([
                ['status', '=', 1],
                ['app_type', '=', 'admin'],
//                ['type', 'in', [0, 1, 2]]
            ])
            ->where($where)
            ->order('id asc, type asc, sort desc')
            ->select()->toArray();
        foreach($menu as &$v){
            $v['path'] = 1;
        }
        return $menu;
    }

    /**
     * 处理菜单分类
     * @param array $menuItems
     * @param int|null $parentId
     * @param string $keyName
     * @param string $parentKeyName
     * @return array
     */
    public static function buildMenuTree(
        array $menu,
        string $parentKey = null,
        string $keyName = 'key',
        string $parentKeyName = 'parent_key')
    {
        $tree = [];
        foreach ($menu as $val) {
            if ($val[$parentKeyName] == $parentKey) {
                $val['children']  = self::buildMenuTree($menu,$val[$keyName]);
                $tree[] = $val;
            }
        }
        return $tree;
    }
}