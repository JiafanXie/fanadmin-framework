<?php

namespace core\src\http\service\admin\auth;


use core\src\http\model\auth\AdminModel;
use core\src\http\model\auth\PermissionModel;
use core\src\http\model\auth\RoleModel;
use core\src\http\service\admin\auth\PermissionsService;
use core\base\service\BaseService as Base;

/**
 * 权限类
 * Class AuthService
 * @package app\service\admin\auth
 */
class AuthService extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取菜单猎列表
     * @param array $where
     * @return PermissionModel[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getPermissionsList($where= [])
    {
        $menu = (new PermissionsService())->getPermissionsList($where);
        return $menu;
    }

    /**
     * 处理菜单
     * @param $menuItems
     * @param null $parentId
     * @param string $keyName
     * @param string $parentKeyName
     * @return array
     */
    public function handerPermissionsTree($menuItems,
                                          $parentId = null,
                                          $keyName = 'id',
                                          $parentKeyName = 'parent_id')
    {
        $tree = [];
        foreach ($menuItems as $menuItem) {
            if ($menuItem[$parentKeyName] == $parentId) {
                $menuItem['children']  = $this->handerPermissionsTree($menuItems,$menuItem[$keyName]);
                $tree[] = $menuItem;
            }
        }
        return $tree;
    }

    /**
     * 获取管理员
     * @param $adminId
     * @return AdminModel|array|mixed|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAdminInfo ($adminId) {
        $admin = (new AdminModel())->where('id', $adminId)->findOrFail();
        return $admin;
    }

    /**
     * 获取权限
     * @param $admin
     * @return RoleModel|array|mixed|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRoleInfo ($admin) {
        $rule  = (new RoleModel())->where('id', $admin->role_id)->findOrFail();
        return $rule;
    }

    /**
     * 获取用户有权限的菜单
     * @param $adminId
     * @param array $menu
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function userPermissions($adminId, $menu = [])
    {
        $admin = $this->getAdminInfo($adminId);
        $rule  = $this->getRoleInfo($admin);
        $where = [
//            ['type', '<>', 2],
        ];
        if ($admin->is_super !=1) {
            array_push($where,['permission_mark','in',$rule->rules]);
        }
        $menu =  $this->getPermissionsList($where);
        return $menu;
        $treePermissionsTree = $this->handerPermissionsTree($menu);
        return $treePermissionsTree;
    }
}