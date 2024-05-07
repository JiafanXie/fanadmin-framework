<?php

namespace FanAdmin\base\model;


use think\facade\Db;
use think\Model;
use FanAdmin\traits\AuthMethod;

/**
 * 基础模型
 * Class BaseModel
 * @package core\base\model
 */
class BaseModel extends Model
{
    /**
     * traits
     */
    use AuthMethod;

    /**
     * 获取模型字段
     * @return array
     */
    public function getModelColumn()
    {
        $table_name = $this->getTable();
        $sql = 'SHOW TABLE STATUS WHERE 1=1 ';
        $tablePrefix = config('database.connections.mysql.prefix');
        if (!empty($table_name)) {
            $sql .= "AND name='" .$table_name."'";
        }
        $tables = Db::query($sql);
        $table_info = $tables[0] ?? [];
        $table_name = str_replace($tablePrefix, '', $table_info['Name']);
        return Db::name($table_name)->getFields();
    }
}
