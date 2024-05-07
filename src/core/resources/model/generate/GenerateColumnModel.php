<?php

namespace FanAdmin\src\http\model\generate;


use FanAdmin\base\model\BaseModel as Base;

/**
 * 生成代码表字段模型
 * Class GenerateColumnModel
 * @package FanAdmin\src\http\model\generate
 */
class GenerateColumnModel extends Base
{
    /**
     * @var string 数据表主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'generate_column';
}