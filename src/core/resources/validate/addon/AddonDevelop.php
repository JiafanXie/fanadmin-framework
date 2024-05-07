<?php

namespace app\validate\addon;

use think\Validate;

/**
 * 开发插件模型
 * Class AddonDevelop
 * @package app\validate\addon
 */
class AddonDevelop extends Validate
{
    protected $rule = [
        'key' => 'require|regex:/^[a-zA-Z][a-zA-Z0-9_]{0,19}$/',
        'type' => 'require|checkType',
    ];

    protected $message = [
        'key.require' => 'validate_addon.key_require',
        'key.regex' => 'validate_addon.key_regex',
        'type.require' => 'validate_addon.type_require',
    ];

    protected $scene = [
        "add" => ['key', 'type'],
        "edit" => ['type']
    ];

    /**
     * 自定义验证type
     * @param $value
     * @param $rule
     * @param array $data
     * @return bool|string
     */
    protected function checkType($value, $rule, $data = [])
    {
        return (!empty($value) && isset(['app' => '应用', 'addon' => '插件'][$value])) ? true : ['app' => '应用', 'addon' => '插件'][$value];
    }
}
