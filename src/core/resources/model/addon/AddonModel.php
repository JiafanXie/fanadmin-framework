<?php

namespace core\src\http\model\addon;


use core\base\model\BaseModel as Base;

/**
 * 插件模型
 * Class AddonModel
 * @package core\src\http\model\addon
 */
class AddonModel extends Base
{
    /**
     * @var string[] 自动时间戳
     */
    protected $type = [
        'install_time' => 'timestamp',
    ];

    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'addon';


    /**
     * 状态名称
     * @param $value
     * @param $data
     * @return mixed|string
     */
    public function getStatusNameAttr($value, $data)
    {
        return [
            1 => '启用', 2 => '禁用'
            ][$data['status']] ?? '';
    }

    /**
     * logo图
     * @param $value
     * @param $data
     * @return string
     */
    public function getIconAttr($value, $data)
    {
        return addonResource($data['key'], 'icon.png');
    }

    /**
     * 封面图
     * @param $value
     * @param $data
     * @return string
     */
    public function getCoverAttr($value, $data)
    {
        return addonResource($data['key'], 'cover.png');
    }

    /**
     * 插件名称搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTitleAttr($query, $value, $data)
    {
        if ($value) {
            $query->whereLike('title', '%' . $value . '%');
        }
    }
}