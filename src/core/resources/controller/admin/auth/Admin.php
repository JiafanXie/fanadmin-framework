<?php

namespace core\src\addon\auth\src\admin\controller\auth;


use app\Request;
use app\service\admin\auth\AdminService;
use core\base\controller\BaseAdminApiController;
use think\App;


/**
 * 管理员控制器
 * Class Admin
 * @package core\src\addon\auth\src\admin\controller\auth
 */
class Admin extends BaseAdminApiController {

    /**
     * 初始化
     */
    public function initialize()
    {
        parent::initialize();
        $this->service = new AdminService();
    }

    /**
     * 管理员列表
     * @param Request $request
     * @return \think\response\Json
     */
    public function list (Request $request) {
        $data = $request->params([
            ["username", ""]
        ]);
        return success($this->service->getList($data));
    }

    /**
     * 管理员详情
     * @param int $id (管理员id)
     * @return \think\response\Json
     */
    public function info(int $id)
    {
        return success($this->service->getInfo($id));
    }

    /**
     * 管理员添加
     * @param Request $request
     * @return \think\response\Json
     */
    public function add(Request $request)
    {
        $data = $request->params([
            ["role_id", 0],
            ["username", ""],
            ["nickname", ""],
            ["mobile", ""],
            ["avatar", ""],
            ["email", ""],
            ["status", ""],

        ]);
        $this->validate($data, 'app\validate\auth\Admin.add');
        $id = $this->service->add($data);
        if($id)
        {
            return success('添加成功', ['id' => $id]);
        }
        return error('添加失败');
    }

    /**
     * 管理员编辑
     * @param int $id (管理员id)
     * @return \think\response\Json
     */
    public function edit(Request $request, int $id)
    {
        $data = $request->params([
            ["role_id", 0],
            ["username", ""],
            ["nickname", ""],
            ["mobile", ""],
            ["avatar", ""],
            ["email", ""],
            ["status", ""],

        ]);
        $this->validate($data, 'app\validate\auth\Admin.edit');
        if($this->service->edit($id, $data))
        {
            return success('保存成功');
        }
        return error('保存失败');
    }

    /**
     * 管理员删除 支持批量删除
     * @param string $id (管理员ids)
     * @return \think\response\Json
     */
    public function delete(string $ids){
        $res = $this->service->delete($ids);
        if($res)
        {
            return success('删除成功', $res);
        }
        return error('删除失败');
    }

    /**
     * 管理员修改密码
     * @param Request $request
     * @param int $id
     * @return \think\response\Json
     */
    public function editPassword(Request $request, int $id)
    {
        $data = $request->params([
            ["password", ""],
            ["rpassword", ""],
        ]);
        if ($data['password'] != $data['rpassword']) return error('两次密码不一样');
        if($this->service->editPassword($id, $data))
        {
            return success('保存成功');
        }
        return error('保存失败');
    }
}