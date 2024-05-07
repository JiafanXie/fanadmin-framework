<?php

namespace core\src\http\service\admin\auth;

use core\src\http\model\auth\RoleModel as Role;
use core\base\service\BaseService as Base;

/**
 * 角色服务层
 * Class RoleService
 * @package app\service\admin\role
 */
class RoleService extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new Role();
    }

    /**
     * 角色列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        $field = 'parent_id, 
            name, 
            code, 
            description, 
            rules, 
            status';
        $order = '';
        $searchModel = $this->model->where([])->withSearch(['"id","parent_id","name","code","description","rules","status","create_time","update_time"'], $where)->field($field)->order($order);
        $list = $this->pageQuery($searchModel);
        return $list;
    }

    /**
     * 角色详情
     * @param int $id (角色id)
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = 'parent_id, 
            name, 
            code, 
            description, 
            rules, 
            status';
        $info = $this->model->field($field)->where([['id', "=", $id]])->findOrEmpty()->toArray();
        return $info;
    }

    /**
     * 角色添加
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $res = $this->model->create($data);
        return $res->id;
    }

    /**
     * 角色编辑
     * @param int $id (角色id)
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $this->model->where([
            ['id', '=', $id]
        ])->update($data);
        return true;
    }

    /**
     * 角色删除 支持批量删除
     * @param int $ids (角色ids)
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

    

}
