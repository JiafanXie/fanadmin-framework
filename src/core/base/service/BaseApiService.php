<?php

namespace FanAdmin\base\service;


/**
 * api基础服务层
 * Class BaseApiService
 * @package core\base\service
 */
class BaseApiService extends BaseService
{
    /**
     * @var 用户名
     */
    protected $username;

    /**
     * @var 用户id
     */
    protected $uid;

    /**
     * @var 用户
     */
    protected $user;

    public function __construct()
    {
        parent::__construct();
    }
}