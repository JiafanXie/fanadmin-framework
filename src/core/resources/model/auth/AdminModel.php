<?php

namespace FanAdmin\src\http\model\auth;

use FanAdmin\base\model\BaseModel as Base;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;
use think\model\relation\HasOne;


/**
 * 管理员模型
 * Class AdminModel
 * @package FanAdmin\src\http\model\auth
 */
class AdminModel extends Base
{
    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'admin';
    
    
    /**
     * 搜索器:后台管理员
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
     * 搜索器:后台管理员角色组
     * @param $value
     * @param $data
     */
    public function searchRoleIdAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("role_id", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员用户名
     * @param $value
     * @param $data
     */
    public function searchUsernameAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("username", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员昵称
     * @param $value
     * @param $data
     */
    public function searchNicknameAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("nickname", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员手机号
     * @param $value
     * @param $data
     */
    public function searchMobileAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("mobile", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员密码
     * @param $value
     * @param $data
     */
    public function searchPasswordAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("password", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员密码盐
     * @param $value
     * @param $data
     */
    public function searchSaltAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("salt", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员头像
     * @param $value
     * @param $data
     */
    public function searchAvatarAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("avatar", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员邮箱
     * @param $value
     * @param $data
     */
    public function searchEmailAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("email", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员登录失败次数
     * @param $value
     * @param $data
     */
    public function searchLoginFailAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("login_fail", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员登录时间
     * @param $value
     * @param $data
     */
    public function searchLoginTimeAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("login_time", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员登录 ip
     * @param $value
     * @param $data
     */
    public function searchLoginIpAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("login_ip", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员是否是超级管理员
     * @param $value
     * @param $data
     */
    public function searchIsSuperAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("is_super", $value);
        }
    }
    
    /**
     * 搜索器:后台管理员状态
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
     * 搜索器:后台管理员创建时间
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
     * 搜索器:后台管理员更新时间
     * @param $value
     * @param $data
     */
    public function searchUpdateTimeAttr($query, $value, $data)
    {
       if ($value) {
            $query->where("update_time", $value);
        }
    }

    /**
     * 登录时间修改器
     * @param $value
     * @return string
     */
    public function getLoginTimeAttr($value)
    {
        return date('Y-m-d H:i:s', $value);
    }
    
    /**
     * @return HasMany
     */
    public function adminLog()
    {
        return $this->hasMany(\app\model\log\AdminLogModel::class, 'uid', 'id');
    }

    /**
     * @return HasOne
     */
    public function role()
    {
        return $this->hasOne(\app\model\auth\RoleModel::class, 'id', 'role_id');
    }


    
}
