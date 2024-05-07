<?php

namespace app\validate\auth;


use FanAdmin\base\BaseValidate;

/**
 * 角色验证器
 * Class Role
 * @package addon\app\validate\role
 */
class Role extends BaseValidate
{
    /**
     * @var string[] 验证规则
     */
    protected $rule = [
        'parent_id' => 'require',
        'name' => 'require',
        'code' => 'require',
        'rules' => 'require',
        'status' => 'require',
   ];

    /**
     * @var array[] 提示信息
     */
    protected $message = [
        'parent_id.require' => ['PARENT_ID.required', '[parent_id]'],
        'name.require' => ['NAME.required', '[name]'],
        'code.require' => ['CODE.required', '[code]'],
        'rules.require' => ['RULES.required', '[rules]'],
        'status.require' => ['STATUS.required', '[status]'],
   ];

    /**
     * @var \string[][] 场景
     */
    protected $scene = [
        "add" => [
        'parent_id', 
        'name', 
        'code', 
        'description', 
        'rules', 
        'status'
        ],
        "edit" => [
        'parent_id', 
        'name', 
        'code', 
        'description', 
        'rules', 
        'status'
        ]
   ];
}
