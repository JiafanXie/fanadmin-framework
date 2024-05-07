<?php

namespace FanAdmin\base\controller;


use core\components\guard\GuardDriver;

/**
 * adminapi控制器基类
 * Class BaseAdminApiController
 * @package core\base\controller
 */
class BaseAdminApiController extends BaseController {
    /**
     * 初始化
     */
    public function initialize()
    {
        $this->auth = new GuardDriver('adminApi');
    }
}