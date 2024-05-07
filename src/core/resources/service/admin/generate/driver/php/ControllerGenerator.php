<?php

namespace core\src\http\service\admin\generate\driver\php;

use core\src\http\service\admin\generate\driver\BaseGenerator;
use think\helper\Str;

/**
 * 控制器生成器
 * Class ControllerGenerator
 * @package core\lib\generator\driver
 */
class ControllerGenerator extends BaseGenerator
{
    /**
     * 替换模板中的变量
     * @return mixed|void
     */
    public function replaceText()
    {
        $text    = $this->replaceFileText(
            [
            '<{namespace}>',
            '<{use}>',
            '<{className}>',
            '<{classComment}>',
            '<{package}>',
            '<{notes}>',
            '<{author}>',
            '<{date}>',
            '<{validate}>',
            '<{addParam}>',
            '<{editParam}>',
            '<{searchParam}>',
            '<{with}>'
        ], [
            $this->getNameSpace(),
            $this->getUse(),
            $this->getUppercaseCaseClassName(),
            $this->getClassComment(),
            $this->getPackageName(),
            $this->getNotes(),
            $this->getAuthor(),
            $this->getNoteDate(),
            $this->getValidate(),
            $this->getAddField(),
            $this->getEditField(),
            $this->getSearchParams(),
            $this->getWithController()
        ], $this->getTplPath('php\Controller'));
        $this->setText($text);
    }

    /**
     * 获取命名空间
     * @return string
     */
    public function getNameSpace()
    {
        if (!empty($this->addonName)) {
            if (!empty($this->moduleName)) {
                return "namespace addon\\".$this->addonName."\\app\\admin\\controller\\" . $this->moduleName . ';';
            }
        } else {
            if (!empty($this->moduleName)) {
                return "namespace app\\admin\\controller\\" . $this->moduleName . ';';
            }
        }
        return "namespace app\\admin\\controller;";
    }

    /**
     * 验证
     * @return string
     */
    public function getValidate()
    {
        if (!empty($this->addonName)) {
            return 'addon\\'.$this->addonName.'\\app\validate\\'.$this->moduleName.'\\' . $this->getUppercaseCaseClassName();
        } else {
            return 'app\validate\\'.$this->moduleName.'\\' . $this->getUppercaseCaseClassName();
        }
    }

    /**
     * 添加字段
     * @return string
     */
    public function getAddField()
    {
        $str = '';
        $last_field = end($this->table['fields'])['column_name'];
        foreach ($this->table['fields'] as $v) {
            if ($v['is_lists'] && !$v['is_pk'] && $v['is_insert'] && $v['column_name'] != 'site_id') {
                $str .= '             ["'.$v['column_name'].'", '.$this->getDefault($v['column_type']).']';
                if($last_field != $v['column_name']) $str .= ', '.PHP_EOL;
            }
        }
        return '['.PHP_EOL.$str.PHP_EOL.'        ]';
    }

    /**
     * 编辑字段
     * @return string
     */
    public function getEditField()
    {
        $str = '';
        $last_field = end($this->table['fields'])['column_name'];
        foreach ($this->table['fields'] as $v) {
            if ($v['is_lists'] && !$v['is_pk'] && $v['is_update'] && $v['column_name'] != 'site_id') {
                $str .= '             ["'.$v['column_name'].'", '.$this->getDefault($v['column_type']).']';
                if($last_field != $v['column_name']) $str .= ', '.PHP_EOL;
            }
        }
        return '['.PHP_EOL.$str.PHP_EOL.'        ]';
    }

    /**
     * 搜索参数
     * @return string
     */
    public function getSearchParams()
    {
        $str = '';
        $last_field = end($this->table['fields'])['column_name'];
        foreach ($this->table['fields'] as $v) {
            if ($v['is_lists'] && !$v['is_pk'] && $v['is_search'] && $v['column_name'] != 'site_id') {
                if ($v['query_type'] == 'BETWEEN') {
                    $str .= '             ["'.$v['column_name'].'", '.'["",""]'.']';
                } else {
                    $str .= '             ["'.$v['column_name'].'", '.'""'.']';
                }
                if($last_field != $v['column_name']) $str .= ', '.PHP_EOL;
            }
        }
        if (!empty($str)) {
            $str = rtrim(rtrim($str), ',');
        }
        return $str;
    }

    /**
     * 引用
     * @return string
     */
    public function getUse()
    {
        $tpl = "use app\\admin\\controller\\BaseAdminApiController;" . PHP_EOL;
        if (!empty($this->addonName)) {
            if (!empty($this->moduleName)) {
                $tpl .= "use addon\\" . $this->addonName."\\"."app\\service\\admin\\" . $this->moduleName . "\\" . $this->getUppercaseCaseClassName() . "Service;" . PHP_EOL ;
            } else {
                $tpl .= "use addon\\". $this->addonName."\\service\\admin\\".$this->getLowercaseCaseTableName().'\\' . $this->getUppercaseCaseClassName() . "Service;" . PHP_EOL ;
            }
        } else {
            if (!empty($this->moduleName)) {
                $tpl .= "use app\\service\\admin\\" . $this->moduleName . "\\" . $this->getUppercaseCaseClassName() . "Service;" . PHP_EOL ;
            } else {
                $tpl .= "use app\\service\\admin\\".$this->getLowercaseCaseTableName().'\\' . $this->getUppercaseCaseClassName() . "Service;" . PHP_EOL ;
            }
        }
        return $tpl;
    }

    /**
     * 类注释
     * @return string
     */
    public function getClassComment()
    {
        if (!empty($this->table['table_content'])) {
            $end_str = substr($this->table['table_content'],-3);
            if ($end_str == '表') {
                $table_content = substr($this->table['table_content'],0,strlen($this->table['table_content'])-3);
            } else {
                $table_content = $this->table['table_content'];
            }
            $tpl = $table_content . '控制器';
        } else {
            $tpl = $this->getUppercaseCaseName() . '控制器';
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
                return 'addon\\'.$this->addonName.'\\app\admin\controller\\'.$this->moduleName;
            } else {
                return 'addon\\'.$this->addonName.'\\app\admin\\controller\\';
            }
        } else {
            if(!empty($this->moduleName))
            {
                return 'app\admin\\controller\\'.$this->moduleName;
            }else{
                return 'app\admin\\controller\\';
            }
        }
    }

    /**
     * 文件生成到模块的文件夹路径
     * @return string
     */
    public function getModuleOutDir()
    {
        $dir = $this->basePath .DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR;
        if (!empty($this->moduleName)) {
            $dir .= $this->moduleName . DIRECTORY_SEPARATOR;
            $this->checkDir($dir);
        }
        return $dir;
    }

    /**
     * 文件生成到runtime的文件夹路径
     * @return string
     */
    public function getRuntimeOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = $this->outDir .DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->outDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        if (!empty($this->moduleName)) {
            $dir .= $this->moduleName . DIRECTORY_SEPARATOR;
            $this->checkDir($dir);
        }
        return $dir;
    }

    /**
     * 文件生成到项目中
     * @return string
     */
    public function getObjectOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'controller'.DIRECTORY_SEPARATOR;
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
            $dir = 'addon/'.$this->addonName.'/app/admin/controller/';
        } else {
            $dir = 'server/app/admin/controller/';
        }
        $dir .= $this->moduleName . '/';
        return $dir;
    }

    /**
     * 文件名
     * @return string
     */
    public function getFileName()
    {
        if($this->className) return Str::studly($this->className) . '.php';
        return $this->getUppercaseCaseName() . '.php';
    }

    /**
     * 获取注释名称
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
     * 增加关联控制器方法
     * @return string
     */
    public function getWithController()
    {
        $with = [];
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str = strripos($column['model'],'\\');
                $with[] = Str::camel(substr($column['model'],$str+1));
            }
        }
        $uCaseClassName =  $this->getUppercaseCaseClassName();
        $content = '';
        if (!empty($with)) {
            $with = array_unique($with);
            foreach ($with as $value) {
               $content .= PHP_EOL.'    public function get'.Str::studly($value).'All(){'.PHP_EOL.'         return success(( new '.$uCaseClassName.'Service())->get'.Str::studly($value).'All());'.PHP_EOL.'    }'.PHP_EOL;
            }
        }
        return $content;
    }
}
