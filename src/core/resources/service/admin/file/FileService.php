<?php

namespace core\src\http\service\admin\file;


use core\components\storage\StorageDriver;
use core\src\http\model\file\FileModel as File;
use app\service\admin\config\ConfigService;
use core\base\service\BaseService as Base;
use core\exception\AdminException;


/**
 * 文件管理类
 * Class FileService
 * @package app\service\admin\file
 */
class FileService extends Base
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new File();
    }

    /**
     * 获取文件域名
     * @param string $uri
     * @param string $type
     * @return string
     */
    public function getFileUrl(string $uri = '', string $type = '') : string
    {
        if (strstr($uri, 'http://'))  return $uri;
        if (strstr($uri, 'https://')) return $uri;
        $info = (new ConfigService())->get('STORAGE');
        if (!empty($info)) {
            if ($info['default'] === 'local') {
                if($type == 'public_path') {
                    return public_path(). $uri;
                }
                $domain = request()->domain();
            } else {
                $storage = $info[$info['default']];
                $domain = $storage ?  $storage['domain'] : '';
            }
        } else {
            $domain = request()->domain();
        }
        return $this->format($domain, $uri);
    }

    /**
     * @notes 转相对路径
     * @param $uri
     * @return mixed
     */
    public function setFileUrl($uri)
    {
        $info = (new ConfigService())->get('STORAGE');
        if (empty($info)) {
            $domain = request()->domain();
            return str_replace($domain.'/', '', $uri);
        }
        if ($info['default'] === 'local') {
            $domain = request()->domain();
        } else {
            $storage = $info[$info['default']];
            $domain  = $storage ?  $storage['domain'] : '';
        }
        return str_replace($domain.'/', '', $uri);
    }


    /**
     * @notes 格式化url
     * @param $domain
     * @param $uri
     * @return string
     */
    public function format($domain, $uri)
    {
        // 处理域名
        $domainLen = strlen($domain);
        $domainRight = substr($domain, $domainLen -1, 1);
        if ('/' == $domainRight) {
            $domain = substr_replace($domain,'',$domainLen -1, 1);
        }

        // 处理uri
        $uriLeft = substr($uri, 0, 1);
        if('/' == $uriLeft) {
            $uri = substr_replace($uri,'',0, 1);
        }

        return trim($domain) . '/' . trim($uri);
    }

    /**
     * 文件列表
     * @param array $where
     * @return array
     * @throws \think\db\exception\DbException
     */
    public function getList(array $where = [])
    {
        $order = '';
        $search_model = $this->model->order($order);
        $list = $this->pageQuery($search_model);
        return $list;
    }

    /**
     * 获取所有数据
     * @param array $where
     * @return File[]|array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getAllList (array $where = []) {
        $list = $this->model->where($where)->select();
        return $list;
    }

    /**
     * 添加
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $res = $this->model->create($data);
        return $res;
    }

    /**
     * @param int $id
     * @return File|array|mixed|\think\Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getInfo(int $id) {
        $res = $this->model->findOrFail($id);
        return $res;
    }
    public function delete (string $ids) {
        $where = [
            ['id', 'in', $ids]
        ];
        $list = $this->getAllList($where);
        if(empty($list)) throw new AdminException('PLEACE_SELECT_IMAGE');
        foreach($list as $v){
            $file_driver = (new StorageDriver())->getProviderClass($v['storage']);
            //读取上传附件的信息用于后续得校验和数据写入,删除失败直接通过
            $file_driver->delete($v['path']);
        }
        $this->model->delete();
        return true;
    }
}