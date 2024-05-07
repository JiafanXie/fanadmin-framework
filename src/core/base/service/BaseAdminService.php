<?php

namespace FanAdmin\base\service;


/**
 * admin基础服务层
 * Class BaseAdminService
 * @package core\base\service
 */
class BaseAdminService extends BaseService
{
    /**
     * @var 用户名
     */
    protected $username;

    /**
     * @var int 用户id
     */
    protected $uid;

    /**
     * @var 用户
     */
    protected $user;

    public function __construct(bool $isFile = false)
    {
        parent::__construct();
    }
}