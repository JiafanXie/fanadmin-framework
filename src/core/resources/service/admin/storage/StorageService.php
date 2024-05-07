<?php

namespace core\src\http\service\admin\storage;


use app\service\admin\config\ConfigService;
use core\base\service\BaseAdminService;
use core\exception\AdminException;

class StorageService extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取存储方式列表
     * @return array[]
     */
    public function getTypeList()
    {
        return [
            'local' => [
                'name' => '本地存储',
                //配置参数
                'params' => [
                ]
            ],
            'qiniu' => [
                'name' => '七牛云存储',
                //配置参数
                'params' => [
                    'bucket'     => '存储空间',
                    'access_key' => 'ACCESS_KEY',
                    'secret_key' => 'SECRET_KEY',
                    'domain'     => '空间域名'
                ]
            ],
            'aliyun' => [
                'name' => '阿里云存储',
                //配置参数
                'params' => [
                    'bucket'     => '存储空间',
                    'access_key' => 'ACCESS_KEY_ID',
                    'secret_key' => 'ACCESS_KEY_SECRET',
                    'endpoint'   => 'Endpoint',
                    'domain'     => '空间域名'
                ]
            ],
            'tencent' => [
                'name' => '腾讯云存储',
                //配置参数
                'params' => [
                    'bucket'     => '存储空间',
                    'region'     => 'REGION',
                    'access_key' => 'SECRET_ID',
                    'secret_key' => 'SECRET_KEY',
                    'domain'     => '空间域名'
                ]
            ],
        ];
    }

    /**
     * 获取存储方式列表
     * @return array
     */
    public function getList () {
        $configType = (new ConfigService())->get('STORAGE');
        if (empty($configType)) {
            $configType['default'] = 'local';
        }
        $storageTypeList = $this->getTypeList();
        $list = [];
        foreach ($storageTypeList as $k => $v) {
            $data = [];
            $data['storage_type'] = $k;
            $data['is_use'] = $k == ($configType['default'] ?? '') ? 1 : 0;
            $data['name'] = $v['name'];
            foreach ($v['params'] as $k_param => $v_param) {
                $data['params'][$k_param] = [
                    'name' => $v_param,
                    'value' => $configType[$k][$k_param] ?? ''
                ];
            }
            $list[] = $data;
        }
        return $list;
    }

    /**
     * 获取详情
     * @param string $key
     */
    public function getInfo (string $key) {
        $storageTypeList = $this->getTypeList();
        if (!array_key_exists($key, $storageTypeList)) throw new AdminException('OSS_TYPE_NOT_EXIST');
        $info = (new ConfigService())->get('STORAGE');
        if (empty($info)) {
            $configType = ['default' => 'local'];
        } else {
            $configType = $info;
        }
        $data = [
            'storage_type' => $key,
            'is_use'       => $key == $configType['default'] ? 1 : 0,
            'name'         => $storageTypeList[$key]['name'],
        ];
        foreach ($storageTypeList[$key]['params'] as $key => $val)  {
            $data['params'][$val] = [
                'name'  => $val,
                'value' => $configType[$key][$val] ?? ''
            ];
        }
        return $data;
    }

    /**
     * 存储配置
     * @param string $storage_type
     * @param array $data
     * @return mixed
     */
    public function edit(string $storage_type, array $data) {
        $storageTypeList = $this->getTypeList();
        if(!array_key_exists($storage_type, $storageTypeList)) throw new AdminException('OSS_TYPE_NOT_EXIST');
        if($storage_type != 'local'){
            $domain = $data['domain'];
            if (!str_contains($domain, 'http://') && !str_contains($domain, 'https://')){
                throw new AdminException('STORAGE_NOT_HAS_HTTP_OR_HTTPS');
            }
        }
        $info = (new ConfigService())->get('STORAGE');
        if (empty($info))  {
            $config['default'] = '';
        } else {
            $config = $info;
        }
        if ($data['is_use'])  {
            $config['default'] = $storage_type;
        } else {
            $config['default'] = '';
        }
        foreach ($storageTypeList[$storage_type]['params'] as $k_param => $v_param) {
            $config[$storage_type][$k_param] = $data[$k_param] ?? '';
        }
        return (new ConfigService())->set('STORAGE', $config);
    }
}