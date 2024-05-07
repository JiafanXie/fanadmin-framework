<?php

namespace FanAdmin\src\http\model\file;


use FanAdmin\base\model\BaseModel as Base;

/**
 * 文件模型
 * Class FileModel
 * @package FanAdmin\src\http\model\file
 */
class FileModel extends Base
{
    /**
     * @var string 主键
     */
    protected $pk = 'id';

    /**
     * @var string 模型名称
     */
    protected $name = 'file';

}