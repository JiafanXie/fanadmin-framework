<?php

<{namespace}>

use app\model\BaseModel;
use think\model\concern\SoftDelete;
use think\model\relation\HasMany;
use think\model\relation\HasOne;
<{with}>

/**
 * <{classComment}>模型
 * Class <{className}>
 * @package <{package}>
 */
class <{className}>Model extends BaseModel
{
    <{softDelete}>
    /**
     * @var string 数据表主键
     */
    protected $pk = '<{pk}>';

    /**
     * @var string 模型名称
     */
    protected $name = '<{tableName}>';
    <{deleteColumn}>
    <{deleteColumnParam}>
    <{searchFunction}>
    <{relationModel}>
    <{selectModel}>
}
