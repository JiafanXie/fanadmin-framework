<?php

namespace core\src\http\service\admin\generate\driver\php;


use core\src\http\service\admin\generate\driver\BaseGenerator;
use think\helper\Str;

/**
 * admin路由生成器
 * Class AdminApiRouteGenerator
 * @package core\lib\generate\driver\php
 */
class AdminApiRouteGenerator extends BaseGenerator
{
    /**
     * 替换模板中的变量
     * @return mixed|void
     */
    public function replaceText()
    {
        $text = $this->replaceFileText(
            [
            '<{notes}>',
            '<{routeGroup}>',
            '<{routeName}>',
            '<{routePath}>',
            '<{withRoute}>',
            '<{route}>',
        ], [
            $this->getNotes(),
            $this->getRouteGroupName(),
            $this->getRouteName(),
            $this->getRoutePath(),
            $this->getWithRoute(),
            $this->getRoute(),
        ],
            $this->getTplPath('php\AdminApiRoute')
        );
        $this->setText($text);
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

    public function getRoute()
    {
        $dir = dirname(root_path());
        if (!empty($this->addonName)) {
            $file = $dir.DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR.'route.php';
        } else {
            $file = $dir.DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR."$this->moduleName".'php';
        }
        if (file_exists($file)) {
            $content    = file_get_contents($file);
            $code_begin = 'USER_CODE_BEGIN -- '.$this->getTableName() . PHP_EOL;
            $code_end   = 'USER_CODE_END -- '.$this->getTableName() . PHP_EOL;
            if (strpos($content,$code_begin) !== false && strpos($content,$code_end) !== false) {
                // 清除相应对应代码块
                $pattern = "/\/\/\s+{$code_begin}[\S\s]+{$code_end}?/";
                $route = preg_replace($pattern, '', $content);
            } else {
                $route = $content;
            }
        } else {
            $route = "<?php

use think\\facade\\Route;

use core\middleware\AdminTokenMiddleware;
use core\middleware\AdminAuthMiddleware;";
        }
        return $route;
    }

    /**
     * 获取类注释
     * @return string
     */
    public function getClassComment()
    {
        if (!empty($this->addonName)) {
            $tpl = $this->addonName . '路由';
        } else {
            if (!empty($this->table['table_content'])) {
                $tpl = $this->table['table_content'] . '路由';
            } else {
                $tpl = $this->getUppercaseCaseName() . '路由';
            }
        }
        return $tpl;
    }

    /**
     * 路由名称
     * @return string
     */
    public function getRouteName()
    {
        //如果是某个模块下的功能，公用一个路由
        if ($this->moduleName &&
            ($this->getLowercaseCaseTableName() != $this->moduleName) &&
            $this->className) {
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
        $dir = $this->basePath . DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR;
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件生成到runtime的文件夹路径（download）
     * @return string
     */
    public function getRuntimeOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = $this->outDir . DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.'.$this->addonName.'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->outDir . 'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR;
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
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'route'.DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件路路径
     * @return string
     */
    public function getFilePath()
    {
        if (!empty($this->addonName)) {
            $dir = 'addon/'.$this->addonName.'/app/admin/route/';
        } else {
            $dir = 'server/app/admin/route/';
        }
        return $dir;
    }

    /**
     * 生成文件名
     * @return string
     */
    public function getFileName()
    {
        if (!empty($this->addonName)) {
            return 'route.php';
        } else {
            return Str::lower($this->moduleName) . '.php';
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
     * 获取路由地址
     * @return string
     */
    public function getRoutePath()
    {
        if (!empty($this->addonName)) {
             return 'addon\\'.$this->addonName.'\\app\admin\\controller\\'.$this->moduleName.'\\'.$this->getUppercaseCaseClassName().'@';
        } else {
            return $this->moduleName.'.'.$this->getUppercaseCaseClassName().'/';
        }
    }

    /**
     * with route内容
     * @return string
     */
    public function getWithRoute()
    {
        if (!empty($this->addonName)) {
            $route_path = 'addon\\'.$this->addonName.'\\app\admin\\controller\\'.$this->moduleName.'\\'.$this->getUppercaseCaseClassName().'@';
        } else {
            $route_path = $this->moduleName.'.'.$this->getUppercaseCaseClassName().'/';
        }
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str     = strripos($column['model'],'\\');
                $with    = Str::camel(substr($column['model'],$str+1));
                $content.= PHP_EOL.'    Route::get('."'".$with."'".','."'".$route_path.'get'.Str::studly($with).'All'."'".');'.PHP_EOL;
            }
        }
        return $content;
    }
}
