<?php

namespace core\src\http\service\admin\generate\driver\vue;


use core\src\http\service\admin\generate\driver\BaseGenerator;
use think\helper\Str;

/**
 * vue语言包生成器
 * Class EditLangGenerator
 * @package core\lib\generator\driver\vue
 */
class EditLangGenerator extends BaseGenerator
{
    /**
     * 替换模板中的变量
     * @return void
     */
    public function replaceText()
    {
        $text = $this->getLang();
        $this->setText($text);
    }

    /**
     * 语言包
     * @return string
     */
    public function getLang()
    {
        if ($this->table['edit_type'] != 2) return '';
        $lang = '';
        foreach ($this->tableColumn as $column) {
            if(($column['is_insert'] || $column['is_update']) && !$column['is_pk']){
                $lang .= '"'.Str::camel($column['column_name']) .'":"'.$column['column_comment'].'",'.PHP_EOL;
            }
        }
        foreach ($this->tableColumn as $column) {
            if (
                ($column['is_insert'] || $column['is_update']) &&
                !$column['is_pk']){
                $placeholder_text = '请输入';
                if (
                    $column['view_type'] == 'select' ||
                    $column['view_type'] == 'checkbox'  ||
                    $column['view_type'] == 'datetime') {
                    $placeholder_text = '请选择';
                }
                if ($column['view_type'] == 'imageSelect') {
                    $placeholder_text = '请上传';
                }
                $lang .= '"'.Str::camel($column['column_name']) .'Placeholder":"'.$placeholder_text.$column['column_comment'].'",'.PHP_EOL;
            }
        }
        if (!empty($this->table['table_content'])) {
            $end_str = substr($this->table['table_content'],-3);
            if ($end_str == '表') {
                $table_content = substr($this->table['table_content'],0,strlen($this->table['table_content'])-3);
            } else {
                $table_content = $this->table['table_content'];
            }
        } else {
            $table_content = '';
        }

        $lang .= '"add'.$this->getUppercaseCaseClassName() .'":"添加'.$table_content.'",'.PHP_EOL;
        $lang .= '"update'.$this->getUppercaseCaseClassName() .'":"编辑'.$table_content.'",'.PHP_EOL;
        $lang .= '"'.$this->getUppercaseCaseClassName() .'DeleteTips":"确定要删除该'.$table_content.'吗？",'.PHP_EOL;
        $lang  = trim(trim($lang), ',');
        return '{'.PHP_EOL.$this->setBlankSpace($lang, "      ").PHP_EOL.'}';
    }

    /**
     * 获取文件生成到模块的文件夹路径
     * @return string
     */
    public function getModuleOutDir()
    {
        if ($this->table['edit_type'] != 2) {
            return '';
        }
        if (!empty($this->addonName)) {
            $dir = $this->outDir . DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->outDir . 'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件生成到runtime的文件夹路径
     * @return string
     */
    public function getRuntimeOutDir()
    {
        if ($this->table['edit_type'] != 2) {
            return '';
        }
        if (!empty($this->addonName)) {
            $dir = $this->outDir . DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        }else{
            $dir = $this->outDir . 'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件生成到项目中
     * @return string
     */
    public function getObjectOutDir()
    {
        if ($this->table['edit_type'] != 2) {
            return '';
        }
        if (!empty($this->addonName)) {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件生成到插件中
     * @return string
     */
    public function getAddonObjectOutDir() {
        $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'zh-cn'.DIRECTORY_SEPARATOR;
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件路径
     * @return string
     */
    public function getFilePath()
    {
        if ($this->table['edit_type'] != 2) {
            return '';
        }
        if (!empty($this->addonName)) {
            $dir = 'addon/'.$this->addonName.'/admin/lang/zh-cn/';

        } else {
            $dir = 'admin/app/lang/zh-cn/';
        }
        return $dir;
    }

    /**
     * 生成的文件名
     * @return string
     */
    public function getFileName()
    {
        if ($this->table['edit_type'] != 2) {
            return '';
        }
        if ($this->className) {
            return $this->moduleName.'.'.Str::lower($this->className).'_edit.json';
        }
        return $this->moduleName.'.'.'edit.json';
    }
}
