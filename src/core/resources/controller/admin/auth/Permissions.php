<?php

namespace core\src\http\controller\admin\auth;


use app\Request;
use core\base\controller\BaseAdminApiController;
use core\src\http\service\admin\auth\PermissionsService;

/**
 * 菜单权限控制器
 * Class Permissions
 * @package core\src\addon\auth\src\admin\controller\auth
 */
class Permissions extends BaseAdminApiController
{
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->service = new PermissionsService();
    }

    /**
     * 菜单列表
     * @param Request $request
     * @return \think\response\Json
     */
    public function list (Request $request) {
        $data = $request->params([
            ["name", ""]
        ]);
        return success($this->service->getList($data));
    }

    /**
     * 菜单详情
     * @param int $id (管理员id)
     * @return \think\response\Json
     */
    public function info(int $id)
    {
        return success($this->service->getInfo($id));
    }

    /**
     * 菜单添加
     * @param Request $request
     * @return \think\response\Json
     */
    public function add (Request $request) {
        $data = $request->params([
            ['app_type', ''],
            ['name', ''],
            ['type', 0],
            ['key', ''],
            ['parent_key', ''],
            ['icon', ''],
            ['api_url', ''],
            ['view_path', ''],
            ['router_path', ''],
            ['methods', ''],
            ['sort', 0],
            ['status', 1],
            ['is_show', 0],
            ['addon', ''],
            ['short_name','']
        ]);
        $this->validate($data, 'app\validate\auth\Permissions.add');
        $res = $this->service->add($data);
        if ($res) {
            return success('添加成功', $res);
        }
        return error('添加失败');
    }

    /**
     * 菜单编辑
     * @param $app_type
     * @param $menu_key
     * @return \think\response\Json
     */
    public function edit(Request $request, int $id)
    {
        $data = $request->params([
            ['name', ''],
            ['parent_key', ''],
            ['type', 0],
            ['icon', ''],
            ['api_url', ''],
            ['router_path', ''],
            ['view_path', ''],
            ['methods', ''],
            ['sort', 0],
            ['status', 1],
            ['is_show', 0],
            ['addon', ''],
            ['short_name','']
        ]);
        $this->validate($data, 'app\validate\auth\Permissions.edit');
        $res = $this->service->edit($id, $data);
        if ($res) {
            return success('保存成功', $res);
        }
        return error('保存失败');
    }

    /**
     * 删除菜单
     * @param string $ids
     * @return \think\response\Json
     */
    public function delete(string $ids)
    {
        $res = $this->service->delete($ids);
        if ($res) {
            return success('删除成功', $res);
        }
        return error('删除失败');
    }

    /**
     * 设置状态
     * @param Request $request
     * @param int $id
     * @return \think\response\Json
     */
    public function status (Request $request, int $id) {
        $data = $request->params([
            ['status', ''],
        ]);
        $res = $this->service->edit($id, $data);
        if ($res) {
            return success('设置成功', $res);
        }
        return error('设置失败');
    }
}