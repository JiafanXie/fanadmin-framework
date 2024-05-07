<?php

namespace FanAdmin\src\http\model\generate;


use FanAdmin\base\model\BaseModel as Base;

/**
 * 生成代码表模型
 * Class GenerateTableModel
 * @package FanAdmin\src\http\model\generate
 */
class GenerateTableModel extends Base
{
    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'generate_table';


    /**
     * 表名搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTableNameAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('table_name', 'like', '%' . $value . '%');
        }
    }

    /**
     * 描述搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchTableContentAttr($query, $value, $data)
    {
        if ($value) {
            $query->where('table_content', 'like', '%' . $value . '%');
        }
    }

    /**
     * 插件搜索器
     * @param $query
     * @param $value
     * @param $data
     */
    public function searchAddonNameAttr($query, $value, $data)
    {
        if ($value) {
            if($value == 2)
            {
                $query->where('addon_name','=','');
            }else{
                $query->where('addon_name', 'like', '%' . $value . '%');
            }

        }
    }

    /**
     * 关联插件表
     * @return \think\model\relation\HasOne
     */
    public function addon()
    {
        return $this->hasOne(\FanAdmin\model\addon\AddonModel::class, 'key', 'addon_name')->joinType('left')->withField('key, title')->bind(['title' => 'title']);
    }
}