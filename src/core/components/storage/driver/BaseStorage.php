<?php

namespace FanAdmin\components\storage\driver;


use FanAdmin\base\driver\BaseDriver;
use think\Exception;

/**
 * 存储引擎抽象类
 * Class BaseStorage
 * @package FanAdmin\components\storage\driver
 */
abstract class BaseStorage  extends BaseDriver {

    /**
     * @var
     */
    protected $file;

    /**
     * @var
     */
    protected $error;

    /**
     * @var
     */
    protected $fileName;

    /**
     * @var
     */
    protected $fileInfo;

    /**
     * @var bool
     */
    protected $isInternal = false;

    /**
     * @var bool 是否上传其他类型文件
     */
    protected $uploadFile = false;

    /**
     * 设置上传的文件信息
     * @param $name
     * @throws Exception
     */
    public function setUploadFile (string $name) {
        // 接收上传的文件
        $this->file = request()->file($name);
        if (empty($this->file)) throw new Exception('未找到上传文件的信息');
        // 校验上传文件后缀
        $limit = array_merge(config('fan.file_image'), config('fan.file_video'), config('fan.file_cert'));
        if (!$this->uploadFile) {
            if (!in_array(strtolower($this->file->extension()), $limit)) throw new Exception('不允许上传' . $this->file->extension() . '后缀文件');
        }
        // 文件信息
        $this->fileInfo = [
            'ext'      => $this->file->extension(),
            'size'     => $this->file->getSize(),
            'mime'     => $this->file->getMime(),
            'name'     => $this->file->getOriginalName(),
            'realPath' => $this->file->getRealPath(),
        ];
        // 生成保存文件名
        $this->fileName = $this->buildSaveName();
    }

    /**
     * 设置上传的文件信息
     * @param $filePath
     */
    public function setUploadFileByReal (string $filePath) {
        // 设置为系统内部上传
        $this->isInternal = true;
        // 文件信息
        $this->fileInfo = [
            'name'     => basename($filePath),
            'size'     => filesize($filePath),
            'tmp_name' => $filePath,
            'error'    => 0,
        ];
        // 生成保存文件名
        $this->fileName = $this->buildSaveName();
    }

    /**
     * 获取
     * @param $url
     * @param $key
     * @return mixed
     */
    abstract protected function fetch (string $url, string $key);

    /**
     * 上传
     * @param $save_dir
     * @return mixed
     */
    abstract protected function upload (string $save_dir);

    /**
     * 删除
     * @param $fileName
     * @return mixed
     */
    abstract protected function delete (string $fileName);

    /**
     * 返回上传后文件路径
     * @return mixed
     */
    abstract public function getFileName ();

    /**
     * 返回文件信息
     * @return mixed
     */
    public function getFileInfo () {
        return $this->fileInfo;
    }

    /**
     * 获取文件真实路径
     * @return mixed
     */
    protected function getRealPath () {
        return $this->fileInfo['realPath'];
    }

    /**
     * 返回错误信息
     * @return mixed
     */
    public function getError () {
        return $this->error;
    }

    /**
     * 生成保存文件名
     * @return string
     */
    private function buildSaveName () {
        // 要上传图片的本地路径
        $realPath = $this->getRealPath();
        // 扩展名
        $ext = pathinfo($this->getFileInfo()['name'], PATHINFO_EXTENSION);
        // 自动生成文件名
        return date('YmdHis') . substr(md5($realPath), 0, 5)
            . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . ".{$ext}";
    }
}
