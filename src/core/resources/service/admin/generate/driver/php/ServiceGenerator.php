<?php

namespace core\src\http\service\admin\generate\driver\php;


use core\src\http\service\admin\generate\driver\BaseGenerator;
use think\helper\Str;

/**
 * service生成器
 * Class ServiceGenerator
 * @package core\lib\generator\driver
 */
class ServiceGenerator extends BaseGenerator
{
    /**
     * 替换模板中的变量
     * @return mixed|void
     */
    public function replaceText()
    {
        $fileds = array_column($this->tableColumn, 'column_name');
        if (in_array('site_id', $fileds)) {
            $tplPath = $this->getTplPath('php/SiteService');
        } else {
            $tplPath = $this->getTplPath('php/Service');
        }
        $text = $this->replaceFileText([
            '<{namespace}>',
            '<{use}>',
            '<{className}>',
            '<{package}>',
            '<{pk}>',
            '<{notes}>',
            '<{author}>',
            '<{date}>',
            '<{fields}>',
            '<{searchFields}>',
            '<{order}>',
            '<{searchModel}>',
            '<{infoSearchModel}>',
            '<{with}>'
        ], [
            $this->getNameSpace(),
            $this->getUse(),
            $this->getUppercaseCaseClassName(),
            $this->getPackageName(),
            $this->getPk(),
            $this->getNotes(),
            $this->getAuthor(),
            $this->getNoteDate(),
            $this->getField(),
            $this->getSearchField(),
            $this->getOrderString(),
            $this->getSearchModel(),
            $this->getInfoSearchModel(),
            $this->getWithAllFunction()
        ], $tplPath);
        $this->setText($text);
    }

    /**
     * 注释名称
     * @return string
     */
    public function getNotes()
    {
        $end_str = substr($this->table['table_content'],-3);
        if ($end_str == '表') {
            return substr($this->table['table_content'],0,strlen($this->table['table_content'])-3);
        } else {
            return $this->table['table_content'];
        }
    }

    /**
     * 字段内容
     * @return string
     */
    public function getField()
    {
        $field = [];
        foreach ($this->tableColumn as $column) {
            if ($column['is_lists']) {
                $field[] = $column['column_name'];
            }
        }
        return implode(", \n            ", $field);
    }

    /**
     * 搜索字段内容
     * @return string
     */
    public function getSearchField()
    {
        $field = [];
        foreach ($this->tableColumn as $column) {
            if (!$column['is_search'] || $column['column_name'] == 'site_id') {
                continue;
            }
            $field[] = '"'.$column['column_name'].'"';
        }
        return implode(", \n          ", $field);
    }

    /**
     * 获取命名空间内容
     * @return string
     */
    public function getNameSpace()
    {
        if (!empty($this->addonName)) {
            if (!empty($this->moduleName)) {
                return "namespace addon\\".$this->addonName."\\app\\service\\admin\\" . $this->moduleName . ';';
            }
        } else {
            if (!empty($this->moduleName)) {
                return "namespace app\\service\\admin\\" . $this->moduleName . ';';
            }
        }
        return "namespace app\\service\\admin;";
    }

    /**
     * use
     * @return string
     */
    public function getUse()
    {
        $tpl = "use app\\model\\" . $this->getUppercaseCaseName() . 'Model as ' . $this->getUppercaseCaseName() . ';';
        if (!empty($this->addonName)) {
            if (!empty($this->moduleName)) {
                $tpl = "use addon\\" . $this->addonName . "\\app\\model\\" . $this->moduleName . "\\" . $this->getUppercaseCaseClassName() . 'Model as ' . $this->getUppercaseCaseClassName() . ';'.PHP_EOL;
            }
        } else {
            if (!empty($this->moduleName)) {
                $tpl = "use app\\model\\" . $this->moduleName . "\\" . $this->getUppercaseCaseClassName() . 'Model as ' . $this->getUppercaseCaseClassName() . ';'.PHP_EOL;
            }
        }
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $tpl.= 'use ' . $column['model'] . 'Model as ' . $column['model'] . ';' . PHP_EOL;
            }
        }
        return $tpl;
    }

    /**
     * package
     * @return string
     */
    public function getPackageName()
    {
        if (!empty($this->addonName)) {
            if (!empty($this->moduleName)) {
                return 'addon\\'.$this->addonName.'\\app\service\\admin\\'.$this->moduleName;
            } else {
                return 'addon\\'.$this->addonName.'\\app\service\\admin\\';
            }
        } else {
            if (!empty($this->moduleName)) {
                return 'app\service\\admin\\'.$this->moduleName;
            } else {
                return 'app\\service\\admin';
            }
        }
    }

    /**
     * 获取文件生成到模块的文件夹路径
     * @return string
     */
    public function getModuleOutDir()
    {
        $dir = $this->basePath . DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
        if (!empty($this->moduleName)) {
            $dir .= $this->moduleName . DIRECTORY_SEPARATOR;
            $this->checkDir($dir);
        }
        return $dir;
    }

    /**
     * 获取文件生成到runtime的文件夹路径
     * @return string
     */
    public function getRuntimeOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = $this->outDir . DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->outDir . 'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        if (!empty($this->moduleName)) {
            $dir .= $this->moduleName . DIRECTORY_SEPARATOR;
            $this->checkDir($dir);
        }
        return $dir;
    }

    /**
     * 获取文件生成到项目中
     * @return string
     */
    public function getObjectOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'service'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        if (!empty($this->moduleName)) {
            $dir .= $this->moduleName . DIRECTORY_SEPARATOR;
            $this->checkDir($dir);
        }
        return $dir;
    }

    /**
     * 文件路径
     * @return string
     */
    public function getFilePath()
    {
        if (!empty($this->addonName)) {
            $dir = 'addon/'.$this->addonName.'/app/service/admin/';
        } else {
            $dir = 'server/app/service/admin/';
        }
        $dir .= $this->moduleName . '/';
        return $dir;
    }

    /**
     * 生成的文件名
     * @return string
     */
    public function getFileName()
    {
        return $this->getUppercaseCaseClassName() . 'Service.php';
    }

    /**
     * 排序
     * @return string
     */
    public function getOrderString()
    {
        if (!empty($this->table['order_type'])) {
            if ($this->table['order_type'] == 1) {
                $type = 'asc';
            } else if($this->table['order_type'] == 2) {
                $type = 'desc';
            }
            foreach ($this->tableColumn as $column) {
                if (!$column['is_order']) {
                    continue;
                }
                $order[] = ''.$column['column_name'].' '.$type.'';
            }
        } else {
            $order = [];
        }
        return implode(',', $order);
    }

    /**
     * 远程下拉（list）
     * @return string
     */
    public function getSearchModel()
    {
        $content = '';
        $with = [];
        $search_field = [];
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str = strripos($column['model'],'\\');
                $with[] = Str::camel(substr($column['model'],$str+1));
            }
            if (!$column['is_search'] || $column['column_name'] == 'site_id') {
                continue;
            }
            $search_field[] = '"'.$column['column_name'].'"';
        }
        $search_field = implode(',', $search_field);
        if (empty($with)) {
            if (in_array('site_id', $this->tableColumn)) {
                $content .= '$this->model->where([ [' . " 'site_id' " . ',"=", $this->site_id ] ])->withSearch([' . "'$search_field'" . '], $where)->field(' . '$field' . ')->order(' . '$order' . ');';
            } else {
                $content .= '$this->model->where([])->withSearch([' . "'$search_field'" . '], $where)->field(' . '$field' . ')->order(' . '$order' . ');';
            }
        } else {
            $with = implode(',', $with);
            if (in_array('site_id', $this->tableColumn)) {
                $content .= '$this->model->where([ [' . " 'site_id' " . ',"=", $this->site_id ] ])->withSearch([' . "'$search_field'" . '], $where)->with(' . "'$with'" . ')->field(' . '$field' . ')->order(' . '$order' . ');';
            } else {
                $content .= '$this->model->where([])->withSearch([' . "'$search_field'" . '], $where)->with(' . "'$with'" . ')->field(' . '$field' . ')->order(' . '$order' . ');';
            }
        }
        return $content;
    }

    /**
     * 远程下拉（info）
     * @return string
     */
    public function getInfoSearchModel()
    {
        $content = '';
        $with    = [];
        $col     = [];
        $pk      = 'id';
        if (empty($this->tableColumn)) {
            $pk = 'id';
        } else {
            foreach ($this->tableColumn as $column) {
                if (!empty($column['model'])) {
                    $str = strripos($column['model'],'\\');
                    $with[] = Str::camel(substr($column['model'],$str+1));
                }
                if ($column['is_pk']) {
                    $pk = $column['column_name'];}
                if (!empty($column['dict_type'])) {
                    if ($column['view_type'] == 'radio') {
                        $col[] = $column['column_name'];
                    }
                }
            }
        }
        if (empty($with)) {
            $content.= '$this->model->field($field)->where([['."'$pk'".', "=", $id]])->findOrEmpty()->toArray();';
        } else {
            $with = implode(',', $with);
            $content.= '$this->model->field($field)->where([['."'$pk'".', "=", $id]])->with('."'$with'".')->findOrEmpty()->toArray();';
        }
        if (!empty($col)) {
            foreach ($col as $v) {
                $content.= PHP_EOL.'   $info['."'".$v."'".'] = strval($info['."'".$v."'])";
            }
        }
        return $content;
    }

    /**
     * 关联表方法
     * @return string
     */
    public function getWithAllFunction()
    {
        $with    = [];
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str = strripos($column['model'],'\\');
                $with[] = Str::camel(substr($column['model'],$str+1));
            }
        }
        if (!empty($with)) {
            foreach ($with as $value) {
                $content.= PHP_EOL.'    public function get'.Str::studly($value).'All(){'.PHP_EOL.'       $'.$value.'Model = new '.Str::studly($value).'();'.PHP_EOL.'       return $'.$value.'Model->where([["site_id","=",$this->site_id]])->select()->toArray();'.PHP_EOL.'    }'.PHP_EOL;
            }
        }
        return $content;
    }
}
