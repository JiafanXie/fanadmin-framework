<?php

namespace core\src\http\service\admin\addon\cloud;

use core\base\service\BaseService;
use core\exception\CommonException;

/**
 * 云授权基类
 * Class BaseCloudService
 * @package core\lib\addon\cloud
 */
class BaseCloudService extends BaseService
{
    protected $auth_code;

    protected $root_path;

    public function __construct()
    {
        parent::__construct();
        $config = (new ConfigCloudService())->getNiucloudConfig();
        $this->auth_code = $config['auth_code'];
        if (empty($this->auth_code)) throw new CommonException('NEED_TO_AUTHORIZE_FIRST');

        $this->root_path = dirname(root_path()) . DIRECTORY_SEPARATOR;
    }

    public function addonPath(string $addon) {
        return root_path() . 'addon' . DIRECTORY_SEPARATOR . $addon . DIRECTORY_SEPARATOR;
    }
}
