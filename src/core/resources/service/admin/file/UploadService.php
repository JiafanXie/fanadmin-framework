<?php

namespace core\src\http\service\admin\file;


use app\service\admin\storage\StorageService;
use core\base\service\BaseAdminService as Base;
use core\components\storage\StorageDriver;
use core\exception\UploadException;
use core\provider\Storage;

/**
 * 文件上传服务
 * Class UploadService
 * @package app\service\admin\file
 */
class UploadService extends Base
{
    /**
     * @var Storage 上传类
     */
    protected $storageDriver;

    /**
     * @var mixed 文件名称
     */
    protected $fileName;

    /**
     * @var mixed 文件信息
     */
    protected $fileInfo;

    /**
     * @var string 存储路径
     */
    protected $saveBaseDir = 'upload/';


    public function __construct(bool $isFile = false)
    {
        parent::__construct($isFile);
        // 获取存储方式
        $config = [];
        $list = (new StorageService())->getList();
        if (!empty($list)) {
            foreach($list as $k => $v){
                if($v['is_use'] == 1){
                    $config = $v['params'] ?? [];
                    $config['storage_type'] = $v['storage_type'];
                }
            }
        }
        if (empty($config)) {
            throw new UploadException('请设置一种上传方式');
        }
        // 上传类
        if ($isFile) {
            $this->storageDriver = new StorageDriver('local');
        } else {
            $this->storageDriver = new StorageDriver($config['storage_type'], $config);
        }
        // 获取设置文件信息
        $this->storageDriver->setUploadFile('file');
        // 文件名名称
        $this->fileName = $this->storageDriver->getFileName();
        // 文件信息
        $this->fileInfo = $this->storageDriver->getFileInfo();
    }

    /**
     * 图片上传
     * @param $groupId
     * @param string $source
     * @param string $saveDir
     * @return array
     */
    public function image(int $groupId, string $source = 'admin')
    {
        try {
            // 校验上传文件后缀
            if (!in_array(strtolower($this->fileInfo['ext']), config('fan.file_image'))) {
                throw new UploadException("上传图片不允许上传". $this->fileInfo['ext'] . "文件");
            }
            // 上传文件
            $saveDir = $this->saveBaseDir . 'image/' .  date('Ymd');
            if (!$this->storageDriver->upload($saveDir)) {
                throw new UploadException($this->storageDriver->getError());
            }
            // 处理文件名称
            $fileInfo = $this->fileInfo;
            if (strlen($this->fileInfo['name']) > 128) {
                $name = substr($this->fileInfo['name'], 0, 123);
                $nameEnd = substr($this->fileInfo['name'], strlen($this->fileInfo['name'])-5, strlen($fileInfo['name']));
                $this->fileInfo['name'] = $name . $nameEnd;
            }
            // 获取图片尺寸
            $path = $saveDir . '/' . str_replace("\\","/", $this->fileName);
            $imageSize = getimagesize($path);
            // 写入数据库中
            $file = (new FileService())->add([
                'source'       => $source,
                'group_id'     => $groupId,
                'url'          => $path,
                'image_width'  => $imageSize[0] ?? 0,
                'image_height' => $imageSize[1] ?? 0,
                'file_md5'     => $this->fileName,
                'filename'     => $this->fileInfo['name'],
                'extension'    => $this->fileInfo['ext'],
                'mimetype'     => $this->fileInfo['mime'],
                'filesize'     => $this->fileInfo['size'],
                'storage'      => 'local'
            ]);
            return [
                'id'        => $file['id'],
                'group_id'  => $file['group_id'],
                'type'      => $file['extension'],
                'name'      => $file['filename'],
                'url'       => (new FileService())->getFileUrl($file['url']),
                'uri'       => $file['url']
            ];
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 视频上传
     * @param $groupId
     * @param string $source
     * @param string $saveDir
     * @return array
     */
    public function video(int $groupId, string $source = 'admin')
    {
        try {
            // 校验上传文件后缀
            if (!in_array(strtolower($this->fileInfo['ext']), config('fan.file_video'))) {
                throw new UploadException("上传视频不允许上传". $this->fileInfo['ext'] . "文件");
            }
            // 上传文件
            $saveDir = $this->saveBaseDir . 'video/' .  date('Ymd');
            if (!$this->storageDriver->upload($saveDir)) {
                throw new UploadException($this->storageDriver->getError());
            }
            // 处理文件名称
            if (strlen($this->fileInfo['name']) > 128) {
                $name = substr($this->fileInfo['name'], 0, 123);
                $nameEnd = substr($this->fileInfo['name'], strlen($this->fileInfo['name'])-5, strlen($fileInfo['name']));
                $this->fileInfo['name'] = $name . $nameEnd;
            }
            $path = $saveDir . '/' . str_replace("\\","/", $this->fileName);
            // 写入数据库中
            $file = (new FileService())->add([
                'source'       => $source,
                'group_id'     => $groupId,
                'url'          => $path,
                'file_md5'     => $this->fileName,
                'filename'     => $this->fileInfo['name'],
                'extension'    => $this->fileInfo['ext'],
                'mimetype'     => $this->fileInfo['mime'],
                'filesize'     => $this->fileInfo['size'],
                'storage'      => 'local'
            ]);
            return [
                'id'        => $file['id'],
                'group_id'  => $file['group_id'],
                'type'      => $file['extension'],
                'name'      => $file['filename'],
                'url'       => (new FileService())->getFileUrl($file['url']),
                'uri'       => $file['url']
            ];
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 证书上传
     * @param $groupId
     * @param string $source
     * @param string $saveDir
     * @return array
     */
    public function cert(int $groupId, string $source = 'admin')
    {
        try {
            // 校验上传文件后缀
            if (!in_array(strtolower($this->fileInfo['ext']), config('fan.file_cert'))) {
                throw new UploadException("上传证书不允许上传". $this->fileInfo['ext'] . "文件");
            }
            // 上传文件
            $saveDir = $this->saveBaseDir.'cert/';
            if (!$this->storageDriver->upload($saveDir)) {
                throw new UploadException($this->storageDriver->getError());
            }
            // 处理文件名称
            if (strlen($this->fileInfo['name']) > 128) {
                $name = substr($this->fileInfo['name'], 0, 123);
                $nameEnd = substr($this->fileInfo['name'], strlen($this->fileInfo['name'])-5, strlen($fileInfo['name']));
                $this->fileInfo['name'] = $name . $nameEnd;
            }
            $path = $saveDir . '/' . str_replace("\\","/", $this->fileName);
            // 写入数据库中
            $file = (new FileService())->add([
                'source'       => $source,
                'group_id'     => $groupId,
                'url'          => $path,
                'file_md5'     => $this->fileName,
                'filename'     => $this->fileInfo['name'],
                'extension'    => $this->fileInfo['ext'],
                'mimetype'     => $this->fileInfo['mime'],
                'filesize'     => $this->fileInfo['size'],
                'storage'      => 'local'
            ]);
            return [
                'id'        => $file['id'],
                'group_id'  => $file['group_id'],
                'type'      => $file['extension'],
                'name'      => $file['filename'],
                'url'       => (new FileService())->getFileUrl($file['url']),
                'uri'       => $file['url']
            ];
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    /**
     * 其它文件上传
     * @param $groupId
     * @param string $source
     * @param string $saveDir
     * @return array
     */
    public function file(int $groupId, string $source = 'admin')
    {
        try {
            // 上传文件
            $saveDir = $this->saveBaseDir . 'file/' .  date('Ymd');
            if (!$this->storageDriver->upload($saveDir)) {
                throw new UploadException($this->storageDriver->getError());
            }
            // 处理文件名称
            if (strlen($this->fileInfo['name']) > 128) {
                $name = substr($this->fileInfo['name'], 0, 123);
                $nameEnd = substr($this->fileInfo['name'], strlen($this->fileInfo['name'])-5, strlen($fileInfo['name']));
                $this->fileInfo['name'] = $name . $nameEnd;
            }
            $path = $saveDir . '/' . str_replace("\\","/", $this->fileName);
            // 写入数据库中
            $file = (new FileService())->add([
                'source'       => $source,
                'group_id'     => $groupId,
                'url'          => $path,
                'file_md5'     => $this->fileName,
                'filename'     => $this->fileInfo['name'],
                'extension'    => $this->fileInfo['ext'],
                'mimetype'     => $this->fileInfo['mime'],
                'filesize'     => $this->fileInfo['size'],
                'storage'      => 'local'
            ]);
            return [
                'id'        => $file['id'],
                'group_id'  => $file['group_id'],
                'type'      => $file['extension'],
                'name'      => $file['filename'],
                'url'       => (new FileService())->getFileUrl($file['url']),
                'uri'       => $file['url']
            ];
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }
}