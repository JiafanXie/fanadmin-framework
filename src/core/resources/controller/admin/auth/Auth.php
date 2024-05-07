<?php

namespace core\src\http\controller\admin\auth;


use core\src\http\service\admin\auth\AuthService;
use core\base\controller\BaseAdminApiController;
use think\App;


/**
 * 用户权限控制器
 * Class Auth
 * @package app\admin\controller\system
 */
class Auth extends BaseAdminApiController {

    /**
     * 初始化
     */
    public function initialize()
    {
        parent::initialize();
        $this->service = new AuthService();
    }

    /**
     * 管理员个人资料
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function info()
    {
        $admin = $this->auth->user();
        $permission = $this->service->userPermissions(1);
        $admin = [
            'permission' => $permission,
            'user'       => $admin,
        ];
        return success($admin);
    }

    /**
     * 用户菜单
     * @return \think\response\Json
     */
    public function userPermissions()
    {
//        $admin   = $this->auth->user();
        $menu = $this->service->userPermissions(1, 1);
        $data = [
            'menu' => $menu,
            'dashboardGrid' => [
                "welcome",
                "ver",
                "time",
                "progress",
                "echarts",
                "about"
            ],
            'permissions' => [
                "list.add",
                "list.edit",
                "list.delete",
                "user.add",
                "user.edit",
                "user.delete"
            ]
        ];
        return success($data);
    }
}