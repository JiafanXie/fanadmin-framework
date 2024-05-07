<?php

namespace FanAdmin\src\http\model\auth;

use FanAdmin\base\model\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;
use think\model\relation\HasOne;

/**
 * 角色模型模型
 * Class RoleModel
 * @package FanAdmin\src\http\model\auth
 */
class RoleModel extends BaseModel
{
    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'role';
    
    
    /**
     * 搜索器:角色
     * @param $value
     * @param $data
     */
    public function searchIdAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("id", $value);
        }
    }
    
    /**
     * 搜索器:角色上级ID
     * @param $value
     * @param $data
     */
    public function searchParentIdAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("parent_id", $value);
        }
    }
    
    /**
     * 搜索器:角色角色名称
     * @param $value
     * @param $data
     */
    public function searchNameAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("name", $value);
        }
    }
    
    /**
     * 搜索器:角色标识
     * @param $value
     * @param $data
     */
    public function searchCodeAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("code", $value);
        }
    }
    
    /**
     * 搜索器:角色描述
     * @param $value
     * @param $data
     */
    public function searchDescriptionAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("description", $value);
        }
    }
    
    /**
     * 搜索器:角色权限规则
     * @param $value
     * @param $data
     */
    public function searchRulesAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("rules", $value);
        }
    }
    
    /**
     * 搜索器:角色状态
     * @param $value
     * @param $data
     */
    public function searchStatusAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("status", $value);
        }
    }
    
    /**
     * 搜索器:角色创建时间
     * @param $value
     * @param $data
     */
    public function searchCreateTimeAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("create_time", $value);
        }
    }
    
    /**
     * 搜索器:角色更新时间
     * @param $value
     * @param $data
     */
    public function searchUpdateTimeAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("update_time", $value);
        }
    }
    
    
    
    
}
