<?php

namespace FanAdmin\components\storage\driver;


use FanAdmin\exception\UploadException;

/**
 * 本地文件驱动
 * Class Local
 * @package FanAdmin\lib\storage\provider
 */
class Local extends BaseStorage {

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
        $info = $this->file->move($save_dir, $this->fileName);
        if (empty($info)) {
            $this->error = $this->file->getError();
            return false;
        }
        return true;
    }

    /**
     * 删除
     * @param $fileName
     * @return bool|mixed
     */
    public function delete ($fileName) {
        $check = strpos($fileName, '/');
        if ($check !== false && $check == 0) {
            $fileName = substr_replace($fileName,"",0,1);
        }
        $filePath = public_path() . "{$fileName}";
        return !file_exists($filePath) ?: unlink($filePath);
    }

    /**
     * 获取
     * @param string $url
     * @param string|null $key
     * @return bool
     */
    public function fetch(string $url, ?string $key)
    {
        try {
            mkdirsOrNotexist(dirname($key), 0777);
            $content = @file_get_contents($url);
            if (!empty($content)) {
                file_put_contents($key, $content);
//                $fp = fopen($key, "w");
//                fwrite($fp, $content);
//                fclose($fp);
            } else {
                throw new UploadException('获取失败');
            }
            return true;
        } catch ( \Exception $e ) {
            throw new UploadException($e->getMessage());
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
