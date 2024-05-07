<?php

namespace FanAdmin\components\storage\driver;


use Exception;
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;

/**
 * 七牛云存储引擎
 * Class Qiniu
 * @package FanAdmin\lib\storage\engine
 */
class Qiniu extends BaseStorage {

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
     */
    public function upload ($save_dir) {
        // 要上传图片的本地路径
        $realPath = $this->getRealPath();
        // 构建鉴权对象
        $auth = new Auth($this->config['access_key'], $this->config['secret_key']);
        // 要上传的空间
        $token = $auth->uploadToken($this->config['bucket']);
        // 初始化 UploadManager 对象并进行文件的上传
        $uploadMgr = new UploadManager();
        try {
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            $key = $save_dir . '/' . $this->fileName;
            list(, $error) = $uploadMgr->putFile($token, $key, $realPath);
            if ($error !== null) {
                $this->error = $error->message();
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取
     * @param $url
     * @param null $key
     * @return bool
     */
    public function fetch ($url, $key = null) {
        try {
            if (substr($url, 0, 1) !== '/' || strstr($url, 'http://') || strstr($url, 'https://')) {
                $auth = new Auth($this->config['access_key'], $this->config['secret_key']);
                $bucketManager = new BucketManager($auth);
                list(, $err) = $bucketManager->fetch($url, $this->config['bucket'], $key);
            } else {
                $auth = new Auth($this->config['access_key'], $this->config['secret_key']);
                $token = $auth->uploadToken($this->config['bucket']);
                $uploadMgr = new UploadManager();
                list(, $err) = $uploadMgr->putFile($token, $key, $url);
            }
            if ($err !== null) {
                $this->error = $err->message();
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除
     * @param $fileName
     * @return bool
     */
    public function delete ($fileName) {
        // 构建鉴权对象
        $auth = new Auth($this->config['access_key'], $this->config['secret_key']);
        // 初始化 UploadManager 对象并进行文件的上传
        $bucketMgr = new BucketManager($auth);
        try {
            list($res, $error) = $bucketMgr->delete($this->config['bucket'], $fileName);
            if ($error !== null) {
                $this->error = $error->message();
                return false;
            }
            return true;
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 返回文件路径
     * @return mixed
     */
    public function getFileName () {
        return $this->fileName;
    }
}
