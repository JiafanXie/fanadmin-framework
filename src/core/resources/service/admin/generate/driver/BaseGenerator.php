<?php

namespace core\src\http\service\admin\generate\driver;


use think\helper\Str;

/**
 *  代码生成器基类
 * Class BaseGenerator
 * @package core\lib\generator\drive
 */
abstract class BaseGenerator
{
    /**
     * @var 模块名
     */
    protected $moduleName;

    /**
     * @var 类名
     */
    protected $className;

    /**
     * @var 表信息
     */
    protected $table;

    /**
     * @var 表字段信息
     */
    protected $tableColumn;

    /**
     * @var 插件名
     */
    protected $addonName;

    /**
     * @var 文件的文字
     */
    protected $text;

    /**
     * @var string 基础文件目录
     */
    protected $basePath;

    /**
     * @var string root目录
     */
    protected $rootPath;

    /**
     * @var string 模板文件夹
     */
    protected $tplDir;

    /**
     * @var string 生成的文件路径
     */
    protected $outDir;

    /**
     * @var string 文件路径
     */
    protected $rootDir;

    /**
     * @return mixed 获取文件生成到模块的文件夹路径
     */
    abstract public function getModuleOutDir();

    /**
     * @return mixed 获取文件生成到runtime的文件夹路径
     */
    abstract public function getRuntimeOutDir();


    /**
     * @return mixed 替换模板文字
     */
    abstract public function replaceText();


    /**
     * @return mixed 生成文件名
     */
    abstract public function getFileName();

    /**
     * @return mixed 获取文件目录
     */
    abstract public function getFilePath();

    public function __construct()
    {
        $this->basePath = base_path();
        $this->rootPath = root_path();
        $this->tplDir = $this->rootPath . 'core'.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR;
        $this->outDir = $this->rootPath . 'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'generator'.DIRECTORY_SEPARATOR;
        $this->checkDir($this->outDir);
        $this->rootDir = dirname(root_path());
    }

    /**
     * 初始化表数据
     * @param array $table
     */
    public function init(array $table)
    {
        // 设置当前表信息
        $this->setTable($table);
        // 设置模块名
        $this->setModuleName($table['module_name'] ?? '');
        // 设置类名
        $this->setClassName($table['class_name'] ?? $table['table_name']);
        // 设置插件名
        $this->setAddonName($table['addon_name'] ?? '');
        // 替换模板中的文本
        $this->replaceText();
    }


    /**
     * 生成文件到模块或runtime目录
     */
    public function generate()
    {
        $paths = [];
        if ($this->table['generate_type'] == 2) {
            // 生成到runtime目录(下载)
            $paths[] = $this->getRuntimeOutDir() . $this->getFileName();
        } else if ($this->table['generate_type'] == 3) {
            // 生成到代码中
            $paths[] = $this->getObjectOutDir() . $this->getFileName();
            // 生成到插件中
            if ($this->addonName && method_exists($this, 'getAddonObjectOutDir'))  $paths[] = $this->getAddonObjectOutDir() . $this->getFileName();
        }
        // 写入内容
        if (!empty($this->getFileName())) {
            foreach ($paths as $path) {
                file_put_contents($path, $this->text);
            }
        }
    }

    /**
     * 文件信息
     * @return array
     */
    public function fileInfo(): array
    {
        if (!empty($this->getFileName())) {
            return
            [
                'name'     => $this->getFileName(),
                'type'     => 'php',
                'content'  => $this->text,
                'file_dir' => $this->getFilePath(),
            ];
        } else {
            return [];
        }
    }

    /**
     * 文件夹不存在则创建
     * @param string $path
     */
    public function checkDir(string $path)
    {
        !is_dir($path) && mkdir($path, 0777, true);
    }

    /**
     * 设置模块名
     * @param string $moduleName
     */
    public function setModuleName(string $moduleName): void
    {
        $this->moduleName = strtolower($moduleName);
        if (empty($this->moduleName)) {
            $this->moduleName = strtolower($this->getLowercaseCaseClassName());
        }
    }

    /**
     * 设置表信息
     * @param $table
     */
    public function setTable($table)
    {
        $this->table       = !empty($table) ? $table : [];
        $this->tableColumn = $table['fields'] ?? [];
    }


    /**
     * 设置类名
     * @param string $className
     */
    public function setClassName(string $className): void
    {
        $this->className = $className;
    }

    /**
     * 设置插件
     * @param string $addonName
     */
    public function setAddonName(string $addonName): void
    {
        $this->addonName = $addonName;
    }

    /**
     * 设置生成文件的文本内容
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * 获取模板路径
     * @param string $templateName
     * @return string
     */
    public function getTplPath(string $templateName): string
    {
        return $this->tplDir . $templateName . '.tpl';
    }


    /**
     * 首字母小写命名
     * @return string
     */
    public function getLowercaseCaseName()
    {
        return Str::camel($this->getTableName());
    }

    /**
     * 首字母大写命名
     * @return string
     */
    public function getUppercaseCaseName()
    {
        return Str::studly($this->getTableName());
    }

    /**
     * 小写表名
     * @return string
     */
    public function getLowercaseCaseTableName()
    {
        return Str::lower($this->getTableName());
    }


    /**
     * 类名称大写
     * @return string
     */
    public function getUppercaseCaseClassName()
    {
        if($this->className) return Str::studly($this->className);
        return $this->getUppercaseCaseName();
    }

    /**
     * 类名称小写
     * @return string
     */
    public function getLowercaseCaseClassName()
    {
        if($this->className) return Str::camel($this->className);
        return $this->getLowercaseCaseName();
    }

    /**
     * 获取表名
     * @return array|string|string[]
     */
    public function getTableName()
    {
        $tablePrefix = config('database.connections.mysql.prefix');
        return str_replace($tablePrefix, '', $this->table['table_name']);
    }

    /**
     * 获取表主键
     * @return mixed|string
     */
    public function getPk()
    {
        $pk = 'id';
        if (empty($this->tableColumn)) {
            return $pk;
        }
        foreach ($this->tableColumn as $item) {
            if ($item['is_pk']) {
                $pk = $item['column_name'];
            }
        }
        return $pk;
    }

    /**
     * 获取缺省值
     * @param string $type
     * @return int|string
     */
    public function getDefault(string $type)
    {
        if (str_starts_with($type, 'set') || str_starts_with($type, 'dict')) {
            $result = '""';
        } elseif (preg_match('/(int|serial|bit)/is', $type)) {
            $result = '0';
        } elseif (preg_match('/(double|float|decimal|real|numeric)/is', $type)) {
            $result = '0.00';
        } elseif (preg_match('/bool/is', $type)) {
            $result = 'false';
        } elseif (str_starts_with($type, 'timestamp')) {
            $result = time();
        } elseif (str_starts_with($type, 'datetime')) {
            $result = '"'.date('Y-m-d H:i:s').'"';
        } elseif (str_starts_with($type, 'date')) {
            $result = '"'.date('Y-m-d H:i:s').'"';
        } else {
            $result = '""';
        }
        return $result;
    }

    /**
     * 获取作者信息
     * @return mixed|string
     */
    public function getAuthor()
    {
        return empty($this->table['author']) ? 'xiejiafan' : $this->table['author'];
    }

    /**
     * 代码生成备注时间
     * @return string
     */
    public function getNoteDate()
    {
        return date('Y/m/d H:i');
    }

    /**
     * 设置空额占位符
     * @param $content
     * @param $blankpace
     * @return string
     */
    public function setBlankSpace($content, $blankpace)
    {
        $content = explode(PHP_EOL, $content);
        foreach ($content as $line => $text) {
            $content[$line] = $blankpace . $text;
        }
        return (implode(PHP_EOL, $content));
    }

    /**
     * 替换文件的内容
     * @param $old
     * @param $new
     * @param $template
     * @return array|false|string|string[]
     */

    public function replaceFileText($old, $new, $template)
    {
        return str_replace($old, $new, file_get_contents($template));
    }
}
