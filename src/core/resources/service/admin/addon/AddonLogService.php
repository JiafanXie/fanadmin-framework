<?php

namespace FanAdmin\src\http\service\admin\addon;

use FanAdmin\src\http\model\addon\AddonLogModel as AddonLog;

/**
 * 安装服务层
 * Class CoreInstallService
 * @package app\service\FanAdmin\install
 */
class AddonLogService extends BaseAddonService
{

    public function __construct()
    {
        parent::__construct();
        $this->model = new AddonLog();
    }

    /**
     * 新增插件日志
     * @param array $params
     * @return true
     */
    public function add(array $params)
    {
        $data = array(
            'type' => $params['type'],
            'key' => $params['key'],
            'from_version' => $params['from_version'],
            'to_version' => $params['to_version'],
        );
        $this->model->create($data);
        return true;
    }

}