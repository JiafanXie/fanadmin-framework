<?php

namespace FanAdmin\src\http\model\user;


use FanAdmin\base\model\BaseModel as Base;

/**
 * 用户表 user 模型
 * Class UserModel
 * @package FanAdmin\src\http\model\user
 */
class UserModel extends Base {

    /**
     * @var string
     */
    protected $name = 'user';

    /**
     * @var array
     */
    protected $type = [];

    /**
     * @var string[]
     */
    protected $hidden = ['password', 'salt'];
}
