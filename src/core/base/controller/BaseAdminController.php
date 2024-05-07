<?php

namespace FanAdmin\base\controller;


use core\provider\auth\AuthDriver;

/**
 * admin控制器基类
 * Class BaseAdminController
 * @package core\base\controller
 */
class BaseAdminController extends BaseController
{
    /**
     * 初始化
     */
    public function initialize()
    {
        $this->auth = new AuthDriver('admin');
    }
}