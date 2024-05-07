<?php

namespace FanAdmin\src\http\service\admin\generate;

use think\App;
use ZipArchive;
use FanAdmin\src\http\service\admin\generate\driver\php\ControllerGenerator;
use FanAdmin\src\http\service\admin\generate\driver\php\ServiceGenerator;
use FanAdmin\src\http\service\admin\generate\driver\php\AdminApiRouteGenerator;
use FanAdmin\src\http\service\admin\generate\driver\php\ModelGenerator;
use FanAdmin\src\http\service\admin\generate\driver\php\ValidateGenerator;
use FanAdmin\src\http\service\admin\generate\driver\vue\IndexGenerator;
use FanAdmin\src\http\service\admin\generate\driver\vue\EditGenerator;
use FanAdmin\src\http\service\admin\generate\driver\vue\EditPageGenerator;
use FanAdmin\src\http\service\admin\generate\driver\vue\ApiGenerator;
use FanAdmin\src\http\service\admin\generate\driver\vue\LangGenerator;
use FanAdmin\src\http\service\admin\generate\driver\vue\EditLangGenerator;
use FanAdmin\src\http\service\admin\generate\driver\php\MenuSqlGenerator;

/**
 * 代码生成器
 * Class Generate
 * @package FanAdmin\lib\generator
 */
class Generate
{
    /**
     * @var string 生成文件路径
     */
    protected $outPath;

    /**
     * @var string runtime目录
     */
    protected $runtimePath;

    /**
     * @var 压缩包名称
     */
    protected $zipName;

    /**
     * @var 压缩包临时路径
     */
    protected $zipTempPath;

    /**
     * @var 代码生成标记
     */
    protected $flag;

    public function __construct()
    {
        $this->outPath     = root_path() . 'public/upload/generator/';
        $this->runtimePath = root_path() . 'public/upload/';
    }

    /**
     * 删除生成文件
     */
    public function delOutFiles()
    {
        // 删除runtime目录制定文件夹
        !is_dir($this->outPath) && mkdirs($this->outPath);
        delTargetDir($this->outPath, false);
    }

    /**
     * 设置生成状态
     * @param $name
     * @param false $status
     */
    public function setFlag(string $name, $status = false)
    {
        $this->flag = $name;
        cache($name, (int)$status, 3600);
    }

    /**
     * 获取生成状态标记
     * @return mixed|object|App
     */
    public function getFlag()
    {
        return cache($this->flag);
    }

    /**
     * 删除标记时间
     */
    public function delFlag()
    {
        cache($this->flag, null);
    }

    /**
     * 生成器相关类
     * @return string[]
     */
    public function getClassGenerator()
    {
        return [
            ControllerGenerator::class,
            ServiceGenerator::class,
            ModelGenerator::class,
            ValidateGenerator::class,
            MenuSqlGenerator::class,
            AdminApiRouteGenerator::class,
            IndexGenerator::class,
            EditGenerator::class,
            EditPageGenerator::class,
            ApiGenerator::class,
            LangGenerator::class,
            EditLangGenerator::class
        ];
    }

    /**
     * 压缩文件
     */
    public function zipFile()
    {
        $fileName = 'generate_' . date('YmdHis') . '.zip';
        $this->zipName = $fileName;
        $this->zipPath = $this->outPath . $fileName;
        $zip = new ZipArchive();
        $zip->open($this->zipPath, ZipArchive::CREATE);
        $this->addFileZip($this->runtimePath, 'generator', $zip);
        $zip->close();
    }

    /**
     * 往压缩包写入文件
     * @param $basePath
     * @param $dirName
     * @param $zip
     */
    public function addFileZip($basePath, $dirName, $zip)
    {
        $handler = opendir($basePath . $dirName);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != '.' && $filename != '..') {
                if (is_dir($basePath . $dirName . '/' . $filename)) {
                    // 当前路径是文件夹
                    $this->addFileZip($basePath, $dirName . '/' . $filename, $zip);
                } else {
                    // 写入文件到压缩包
                    $zip->addFile($basePath . $dirName . '/' . $filename, $dirName . '/' . $filename);
                }
            }
        }
        closedir($handler);
    }

    /**
     * 返回压缩包临时路径
     * @return string
     */
    public function getDownloadUrl()
    {
        return 'upload/generator/' .$this->zipName;
    }

    /**
     * 生成文件
     * @param array $table
     */
    public function generate(array $table)
    {
        foreach ($this->getClassGenerator() as $item) {
            $generator = app()->make($item);
            $generator->init($table);
            $generator->generate();
            $this->setFlag($this->flag, true);
        }
    }

    /**
     * 预览文件
     * @param array $table
     * @return array
     */
    public function preview(array $table)
    {
        $data = [];
        foreach ($this->getGenerator() as $item) {
            $generator = app()->make($item);
            $generator->init($table);
            $file_info = $generator->fileInfo();
            if(!empty($file_info))
            {
                $data[] = $file_info;
            }
        }
        return $data;
    }

    /**
     * 生成类文件
     * @return string[]
     */
    public function getGenerator()
    {
        return [
            ControllerGenerator::class,
            ModelGenerator::class,
            ServiceGenerator::class,
            ValidateGenerator::class,
            MenuSqlGenerator::class,
            AdminApiRouteGenerator::class,
            WebApiGenerator::class,
            LangGenerator::class,
            EditGenerator::class,
            IndexGenerator::class,
            EditPageGenerator::class,
            EditLangGenerator::class
        ];
    }
}
