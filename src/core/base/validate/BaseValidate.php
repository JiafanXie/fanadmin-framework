<?php

namespace FanAdmin\base\validate;


use think\Validate;

/**
 * 验证器验证基类
 * Class BaseValidate
 * @package core\base\validate
 */
class BaseValidate extends Validate
{
    public function __construct()
    {
        parent::__construct();
        $this->parseMsg();
    }

    /**
     * @return \lang
     */
    public function parseMsg()
    {
        if (!empty($this->message)) {
            foreach ($this->message as $key => $value) {
                if (is_array($value)) {
                    return $this->message[$key] = getLang($value[0], $value[1]);
                }
            }
        }
    }
}
