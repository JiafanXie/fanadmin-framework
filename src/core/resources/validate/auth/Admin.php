<?php

namespace app\validate\auth;


use FanAdmin\base\BaseValidate;

/**
 * 后台管理员验证器
 * Class Admin
 * @package addon\app\validate\admin
 */
class Admin extends BaseValidate
{
    /**
     * @var string[] 验证规则
     */
    protected $rule = [
        'role_id' => 'require',
        'username' => 'require',
        'nickname' => 'require',
        'password' => 'require',
        'status' => 'require',
   ];

    /**
     * @var array[] 提示信息
     */
    protected $message = [
        'role_id.require' => ['ROLE_ID.required', '[role_id]'],
        'username.require' => ['USERNAME.required', '[username]'],
        'nickname.require' => ['NICKNAME.required', '[nickname]'],
        'password.require' => ['PASSWORD.required', '[password]'],
        'status.require' => ['STATUS.required', '[status]'],
    ];

    /**
     * @var \string[][] 场景
     */
    protected $scene = [
        "add" => [
        'role_id', 
        'username', 
        'nickname', 
        'mobile', 
        'password',
        'email',
        'login_time',
        'status'
        ],
        "edit" => [
        'role_id', 
        'username', 
        'nickname', 
        'mobile', 
        'password',
        'email',
        'login_time',
        'status'
        ]
   ];
}
