<?php

<{namespace}>


use app\validate\BaseValidate;

/**
 * <{classComment}>
 * Class <{className}>
 * @package <{package}>
 */
class <{className}> extends BaseValidate
{
    /**
     * 定义验证规则
     * 格式：'字段名' =>  ['规则1','规则2'...]
     *
     * @var array
     */
    protected $rule = <{rule}>;

    /**
     * 定义错误信息
     * 格式：'字段名.规则名' =>  '错误信息'
     *
     * @var array
     */
    protected $message = <{message}>;

    /**
     * 场景
     * 格式：
     *
     * @var array
     */
    protected $scene = <{scene}>;
}
