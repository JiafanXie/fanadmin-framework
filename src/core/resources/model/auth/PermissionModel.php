<?php

namespace FanAdmin\src\http\model\auth;


use FanAdmin\base\model\BaseModel as Base;

/**
 * 权限模型
 * Class PermissionModel
 * @package FanAdmin\src\http\model\auth
 */
class PermissionModel extends Base
{
    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'permissions';

    /**
     * @var string[] 追加字段
     */
    protected $append = ['status_name', 'app_type_name', 'type_name', 'status_name', 'show_name'];

    /**
     * 应用类型
     * @param $value
     * @param $data
     * @return string
     */
    public function getAppTypeNameAttr($value, $data)
    {
        if (empty($data['app_type']))
            return '';
        return ['admin' => '后台', 'site' => '站点'][$data['app_type']] ?? '';
    }

    /**
     * 菜单类型
     * @param $value
     * @param $data
     * @return string
     */
    public function getTypeNameAttr($value, $data)
    {
        return [0 => '目录', 1 => '菜单', 2 => '按钮'][$data['type']] ?? '';
    }

    /**
     * 菜单状态
     * @param $value
     * @param $data
     * @return string
     */
    public function getStatusNameAttr($value, $data)
    {
        if (empty($data['status']))
            return '';
        return ['1' => '展示', '2' => '隐藏', '3' => '禁用'][$data['status']] ?? '';
    }

    /**
     * 菜单状态
     * @param $value
     * @param $data
     * @return string
     */
    public function getShowNameAttr($value, $data)
    {
        if (empty($data['is_show']))
            return '';
        return ['1' => '展示', '2' => '隐藏'][$data['is_show']] ?? '';
    }
}