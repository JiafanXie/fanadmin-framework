<?php

namespace core\src\http\service\admin\generate\driver\vue;


use core\src\http\service\admin\generate\driver\BaseGenerator;
use think\helper\Str;

/**
 * vue文件api生成器
 * Class WebApiGenerator
 * @package core\lib\generator\driver
 */
class ApiGenerator extends BaseGenerator
{
    /**
     * 替换模板中的变量
     * @return void
     */
    public function replaceText()
    {
        $old = [
            '<{notes}>',
            '<{uClassName}>',
            '<{lClassName}>',
            '<{routeName}>',
            '<{pk}>',
            '<{className}>',
            '<{moduleName}>',
            '<{routeGroup}>',
            '<{import}>',
            '<{begin}>',
            '<{withRouteApi}>',
            '<{end}>',
        ];
        $new = [
            $this->getNotes(),
            $this->getUppercaseCaseClassName(),
            strtolower($this->getLowercaseCaseClassName()),
            $this->getRouteName(),
            $this->getPk(),
            $this->getUppercaseCaseClassName(),
            $this->moduleName,
            $this->getRouteGroupName(),
            $this->getImport(),
            $this->getBegin(),
            $this->getWithRouteApi(),
            $this->getEnd(),
        ];
        $tplPath = $this->getTplPath('vue/Api');
        $text    = $this->replaceFileText($old, $new, $tplPath);
        $this->setText($text);
    }

    /**
     * 路由名称
     * @return string
     */
    public function getRouteName()
    {
        //如果是某个模块下的功能，公用一个路由
        if ($this->moduleName && ($this->getLowercaseCaseTableName() != $this->moduleName) && $this->className){
            return Str::lower($this->className);
        } else {
            return $this->getLowercaseCaseTableName();
        }
    }

    /**
     * 获取文件生成到模块的文件夹路径
     * @return string
     */
    public function getModuleOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = dirname(app()->getRootPath()) . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'. $this->addonName  .'/api/';
        } else {
            $dir = dirname(app()->getRootPath()) . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 文件内容处理
     * @return array|false|string|string[]|null
     */
    public function getImport()
    {
        $dir = dirname(root_path());
        if (!empty($this->addonName)) {
            $file = $dir.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.$this->moduleName.'.ts';
        } else {
            $file = $dir.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.$this->moduleName.'.ts';
        }
        if (file_exists($file)) {
            $content    = file_get_contents($file);
            $code_begin = 'USER_CODE_BEGIN -- '.$this->getTableName() . PHP_EOL;
            $code_end   = 'USER_CODE_END -- '.$this->getTableName(). PHP_EOL;
            if (strpos($content,$code_begin) !== false && strpos($content,$code_end) !== false) {
                // 清除相应对应代码块
                $pattern = "/\/\/\s+{$code_begin}[\S\s]+{$code_end}?/";
                $import  = preg_replace($pattern, '', $content);
            } else {
                $import = $content;
            }
        } else {
            $import = "import request from '@/utils/request'";
        }
        return $import;
    }

    /**
     * 获取开始
     * @return string
     */
    public function getBegin()
    {
        $begin = '// USER_CODE_BEGIN -- '.$this->getTableName();
        return $begin;
    }

    /**
     * 获取结束
     * @return string
     */
    public function getEnd()
    {
        $end = '// USER_CODE_END -- '.$this->getTableName();
        return $end;
    }

    /**
     * 获取文件生成到runtime的文件夹路径
     * @return string
     */
    public function getRuntimeOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = $this->outDir . 'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->outDir . 'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;
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
        if (!empty($this->addonName)) {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件生成到插件中
     * @return string
     */
    public function getAddonObjectOutDir() {
        $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR;
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 文件路径
     * @return string
     */
    public function getFilePath()
    {
        if (!empty($this->addonName)) {
            $dir = 'addon/'.$this->addonName.'/admin/api/';
        } else {
            $dir = 'admin/app/api/';
        }
        return $dir;
    }

    /**
     * 生成的文件名
     * @return string
     */
    public function getFileName()
    {
        if ($this->moduleName && ($this->getLowercaseCaseTableName() != $this->moduleName)){
            return Str::lower($this->moduleName) . '.ts';
        } else {
            return $this->getLowercaseCaseTableName() . '.ts';
        }
    }

    /**
     * 获取路由分组
     * @return 插件名|模块名
     */
    public function getRouteGroupName()
    {
        if (!empty($this->addonName)) {
            return $this->addonName;
        } else {
            return $this->moduleName;
        }
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
     * with路由
     * @return string
     */
    public function getWithRouteApi()
    {
        if (!empty($this->addonName)) {
            $moduleName = $this->addonName;
        } else {
            $moduleName = $this->moduleName;
        }
        $with    = [];
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str    = strripos($column['model'],'\\');
                $with[] = Str::camel(substr($column['model'],$str+1));
            }
        }
        if (!empty($with)) {
//            $str = strripos($column['model'],'\\');
//            $with = Str::camel(substr($column['model'],$str+1));
//            $content.= ' get'.Str::studly($with).'List,';
//            export function getCompanyList(params: Record<string, any>) {
//            return request.get(`shop/delivery/company`, {params})
//            } $with = Str::camel(substr($column['model'],$str+1));
            foreach ($with as $value) {
                $content.= 'export function getWith'.Str::studly($value).'List(params: Record<string,any>){'.PHP_EOL."    return request.get('".$moduleName.'/'.$value."', {params})".PHP_EOL.'}';
            }
        }
        return $content;
    }
}
