<?php

namespace FanAdmin\components\storage\driver;


use OSS\OssClient;
use OSS\FanAdmin\OssException;

/**
 * 阿里云存储引擎 (OSS)
 * Class Aliyun
 * @package FanAdmin\components\storage\driver
 */
class Aliyun extends BaseStorage {

    /**
     * 初始化
     * @param array $config
     * @return mixed|void
     */
    protected function initialize (array $config) {
        $this->config = $config;
    }

    /**
     * 上传
     * @param $save_dir
     * @return bool
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function upload ($save_dir) {
        try {
            $ossClient = new OssClient(
                $this->config['access_key'],
                $this->config['secret_key'],
                $this->config['domain'],
                true
            );
            $ossClient->uploadFile(
                $this->config['bucket'],
                $save_dir . '/' . $this->fileName,
                $this->getRealPath()
            );
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 获取
     * @param $url
     * @param null $key
     * @return bool
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function fetch ($url, $key = null) {
        try {
            $ossClient = new OssClient(
                $this->config['access_key'],
                $this->config['secret_key'],
                $this->config['domain'],
                true
            );
            $ossClient->putObject(
                $this->config['bucket'],
                $key,
                file_get_contents($url)
            );
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 删除
     * @param $fileName
     * @return bool
     * @throws \OSS\Http\RequestCore_Exception
     */
    public function delete ($fileName) {
        try {
            $ossClient = new OssClient(
                $this->config['access_key'],
                $this->config['access_key'],
                $this->config['domain'],
                true
            );
            $ossClient->deleteObject($this->config['bucket'], $fileName);
        } catch (OssException $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName () {
        return $this->fileName;
    }
}
