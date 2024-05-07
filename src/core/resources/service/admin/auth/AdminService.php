<?php

namespace core\src\http\service\admin\auth;

use app\model\auth\AdminModel as Admin;
use core\base\service\BaseService as Base;

/**
 * 管理员服务层
 * Class AdminService
 * @package app\service\admin\auth
 */
class AdminService extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Admin();
    }

    /**
     * 管理员列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        $field = 'id, 
            role_id, 
            username, 
            nickname, 
            mobile, 
            avatar, 
            email, 
            login_time, 
            login_ip, 
            is_super, 
            status, 
            create_time, 
            update_time';
        $order = '';
        $searchModel = $this->model->with(['role', 'adminLog'])->where([])->withSearch(['"id","role_id","username","nickname","mobile","password","salt","avatar","email","login_fail","login_time","login_ip","is_super","status","create_time","update_time"'], $where)->field($field)->order($order);
        $list = $this->pageQuery($searchModel);
        return $list;
    }

    /**
     * 管理员详情
     * @param int $id (管理员id)
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'id, 
            role_id, 
            username, 
            nickname, 
            mobile, 
            avatar, 
            email, 
            login_time, 
            login_ip, 
            is_super, 
            status, 
            create_time, 
            update_time';
        $info = $this->model->field($field)->where([['id', "=", $id]])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 管理员添加
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $salt         = mt_rand(1000, 9999);
        $data['salt'] = $salt;
        $data['is_super'] = 0;
        $data['password'] = encryptPassword($data['password'], $salt);
        $res = $this->model->create($data);
        return $res->id;
    }

    /**
     * 管理员编辑
     * @param int $id (管理员id)
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $salt         = mt_rand(1000, 9999);
        $data['salt'] = $salt;
        $data['is_super'] = 0;
        $data['password'] = encryptPassword($data['password'], $salt);
        $this->model->where([
            ['id', '=', $id]
        ])->update($data);
        return true;
    }

    /**
     * 管理员删除 支持批量删除
     * @param int $ids (管理员ids)
     * @return bool
     */
    public function delete(int $ids)
    {
        $ids = explode(',', $ids);
        $res = $this->model->where([
            ['id', 'in', $ids]
        ])->delete();
        return $res;
    }

    /**
     * 管理员修改密码
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function editPassword(int $id, array $data)
    {
        $salt         = mt_rand(1000, 9999);
        $data['salt'] = $salt;
        $data['is_super'] = 0;
        $data['password'] = encryptPassword($data['rpassword'], $salt);
        $this->model->where([
            ['id', '=', $id]
        ])->update($data);
        return true;
    }
}
