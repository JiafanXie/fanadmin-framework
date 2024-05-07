<?php

namespace app\validate\auth;


use FanAdmin\base\BaseValidate;

/**
 * 菜单验证器
 * Class Permissions
 * @package addon\app\validate\permissions
 */
class Permissions extends BaseValidate
{
    /**
     * @var string[] 验证规则
     */
    protected $rule = [
        'app_type' => 'require',
        'name' => 'require',
        'short_name' => 'require',
        'key' => 'require',
        'parent_key' => 'require',
        'type' => 'require',
        'icon' => 'require',
        'api_url' => 'require',
        'router_path' => 'require',
        'view_path' => 'require',
        'methods' => 'require',
        'sort' => 'require',
        'status' => 'require',
        'is_show' => 'require',
        'delete_time' => 'require',
        'addon' => 'require',
        'source' => 'require',
        'menu_attr' => 'require',
   ];

    /**
     * @var array[] 提示信息
     */
    protected $message = [
        'app_type.require' => ['APP_TYPE.required', '[app_type]'],
        'name.require' => ['NAME.required', '[name]'],
        'short_name.require' => ['SHORT_NAME.required', '[short_name]'],
        'key.require' => ['KEY.required', '[key]'],
        'parent_key.require' => ['PARENT_KEY.required', '[parent_key]'],
        'type.require' => ['TYPE.required', '[type]'],
        'icon.require' => ['ICON.required', '[icon]'],
        'api_url.require' => ['API_URL.required', '[api_url]'],
        'router_path.require' => ['ROUTER_PATH.required', '[router_path]'],
        'view_path.require' => ['VIEW_PATH.required', '[view_path]'],
        'methods.require' => ['METHODS.required', '[methods]'],
        'sort.require' => ['SORT.required', '[sort]'],
        'status.require' => ['STATUS.required', '[status]'],
        'is_show.require' => ['IS_SHOW.required', '[is_show]'],
        'delete_time.require' => ['DELETE_TIME.required', '[delete_time]'],
        'addon.require' => ['ADDON.required', '[addon]'],
        'source.require' => ['SOURCE.required', '[source]'],
        'menu_attr.require' => ['MENU_ATTR.required', '[menu_attr]'],
   ];

    /**
     * @var \string[][] 场景
     */
    protected $scene = [
        "add" => [
        'app_type', 
        'name', 
        'short_name', 
        'key', 
        'parent_key', 
        'type', 
        'icon', 
        'api_url', 
        'router_path', 
        'view_path', 
        'methods', 
        'sort', 
        'status', 
        'is_show', 
        'delete_time', 
        'addon', 
        'source', 
        'menu_attr'
        ],
        "edit" => [
        'app_type', 
        'name', 
        'short_name', 
        'key', 
        'parent_key', 
        'type', 
        'icon', 
        'api_url', 
        'router_path', 
        'view_path', 
        'methods', 
        'sort', 
        'status', 
        'is_show', 
        'delete_time', 
        'addon', 
        'source', 
        'menu_attr'
        ]
   ];
}
