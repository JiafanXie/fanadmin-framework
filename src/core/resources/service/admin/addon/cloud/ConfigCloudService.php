<?php

namespace core\src\http\service\admin\addon\cloud;

use app\dict\sys\ConfigKeyDict;
use app\service\core\sys\CoreConfigService;
use core\base\BaseCoreService;
use core\base\BaseService;

/**
 * 云配置服务
 * Class ConfigCloudService
 * @package core\lib\addon\cloud
 */
class ConfigCloudService extends BaseService
{

    /**
     * 获取牛云配置
     * @return array|mixed|string[]
     */
    public function getNiucloudConfig(){
        $info = (new CoreConfigService())->getConfig(0, ConfigKeyDict::NIUCLOUD_CONFIG)['value'] ?? [];
        if(empty($info))
        {
            $info = [
                'auth_code' => '',
                'auth_secret' => ''
            ];
        }
        return $info;
    }


}