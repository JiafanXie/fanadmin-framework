<?php

namespace FanAdmin\src\http\model\user;




use FanAdmin\base\model\BaseModel as Base;

/**
 * 第三方登录模型
 * Class ThirdOauthModel
 * @package FanAdmin\src\http\model\user
 */
class ThirdOauthModel extends Base {

    /**
     * @var string
     */
    protected $name = 'third_oauth';

    /**
     * @var array
     */
    protected $type = [];

    /**
     * @var array
     */
    protected $hidden = [];
}
