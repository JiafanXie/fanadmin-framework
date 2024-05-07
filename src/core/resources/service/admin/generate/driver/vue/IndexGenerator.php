<?php

namespace core\src\http\service\admin\generate\driver\vue;


use app\service\core\menu\CoreMenuService;
use core\src\http\service\admin\generate\driver\BaseGenerator;
use think\helper\Str;

/**
 * vue index生成器
 * Class IndexGenerator
 * @package core\lib\generator\driver\vue
 */
class IndexGenerator extends BaseGenerator
{
    /**
     * 替换模板中的变量
     * @return void
     */
    public function replaceText()
    {
        $old = [
            '{searchView}',
            '{searchParam}',
            '{listView}',
            '{notes}',
            '{UCASE_NAME}',
            '{LCASE_NAME}',
            '{uClassName}',
            '{lClassName}',
            '{moduleName}',
            '{editPath}',
            '{pk}',
            '{editView}',
            '{editDialog}',
            '{addEvent}',
            '{editEvent}',
            '{apiPath}',
            '{dictList}',
            '{withApiPath}',
            '{modelData}',
            '{editWithApiPath}'
        ];
        $new = [
            $this->getSearch(),
            $this->getSearchParams(),
            $this->getTable(),
            $this->getNotes(),
            $this->getUppercaseCaseClassName(),
            $this->getLowercaseCaseName(),
            $this->getUppercaseCaseClassName(),
            $this->getUppercaseCaseClassName(),
            $this->moduleName,
            $this->getEditPath(),
            $this->getPk(),
            $this->getEditView(),
            $this->getEditDialog(),
            $this->getAddEvent(),
            $this->getEditEvent(),
            $this->getApiPath(),
            $this->getDictList(),
            $this->getWithApiPath(),
            $this->getModelData(),
            $this->getEditWithApiPath(),
        ];
        $tplPath = $this->getTplPath('vue/Index');
        $text    = $this->replaceFileText($old, $new, $tplPath);
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

    /**
     * 编辑框路径
     * @return string
     */
    public function getEditPath()
    {
        if ($this->table['edit_type'] == 2) return "import { useRouter } from 'vue-router'";
        $path = 'components/';
//        $file_name = str_replace('_', '-', Str::lower($this->getTableName())).'-edit.vue';
        $file_name = 'edit.vue';
        if ($this->className) {
            $file_name = str_replace('_', '-', Str::lower($this->className)) . '-edit.vue';
        }
        if (!empty($this->addonName)) {
            return "import "."Edit from '@/addon/".$this->addonName."/views/".$this->moduleName."/".$path.$file_name."'";
        } else {
            return "import "."Edit from '@/app/views/".$this->moduleName."/".$path.$file_name."'";
        }
    }

    /**
     * 编辑框
     * @return string
     */
    public function getEditView()
    {
        if ($this->table['edit_type'] == 2) return '';
        $file_name = 'edit';
        return '<'.$file_name.' ref="edit'.$this->getUppercaseCaseClassName().'Dialog" @complete="load'.$this->getUppercaseCaseClassName().'List" />';
    }

    /**
     * 编辑框Dialog
     * @return string
     */
    public function getEditDialog()
    {
        if($this->table['edit_type'] == 2) return 'const router = useRouter()';
        return 'const edit'.$this->getUppercaseCaseClassName().'Dialog: Record<string, any> | null = ref(null)';
    }

    /**
     * 添加操作
     * @return string
     */
    public function getAddEvent()
    {
        $class_name = $this->className ? '/'.Str::lower($this->className) : '';
        if($this->table['edit_type'] == 2){
            $route = '';
            if (!empty($this->table['parent_menu'])) {
                $route = '/' .  (new CoreMenuService())->getRoutePathByMenuKey($this->table['parent_menu']);
            }
            //打开新页面
            $content = "router.push('".$route."/".$this->moduleName."/". Str::lower($this->className) ."_edit')";
        } else {
            $content = 'edit'.$this->getUppercaseCaseClassName().'Dialog.value.setFormData()'.PHP_EOL.'edit'.$this->getUppercaseCaseClassName().'Dialog.value.showDialog = true';
        }
        return $this->setBlankSpace($content, '    ');
    }

    /**
     * 编辑
     * @return string
     */
    public function getEditEvent()
    {
        $class_name = $this->className ? '/'.Str::lower($this->className) : '';
        if($this->table['edit_type'] == 2){
            $route = '';
            if (!empty($this->table['parent_menu'])) {
                $route = '/' . (new CoreMenuService())->getRoutePathByMenuKey($this->table['parent_menu']);
            }
            $content = "router.push('".$route."/".$this->moduleName."/". Str::lower($this->className) ."_edit?id='+data.".$this->getPk().")";
        }else{
            $content = 'edit'.$this->getUppercaseCaseClassName().'Dialog.value.setFormData(data)'.PHP_EOL.'edit'.$this->getUppercaseCaseClassName().'Dialog.value.showDialog = true';
        }
        return $this->setBlankSpace($content, '    ');
    }

    /**
     * 获取搜索内容
     * @return string
     */
    public function getSearch()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!$column['is_search'] || $column['is_pk']) {
                continue;
            }
            $old = [
                '{columnComment}',
                '{columnName}',
                '{dictType}',
                '{lClassName}',
                '{lColumnName}',
                '{label}',
                '{value}'
            ];
            if (empty($column['dict_type'])) {
                if (empty($column['model'])) {
                    $new = [
                        $column['column_comment'],
                        $column['column_name'],
                        $column['dict_type'],
                        $this->getLowercaseCaseClassName(),
                        Str::camel($column['column_name']),
                        '',
                        ''
                    ];
                } else {
                    $new = [
                        $column['column_comment'],
                        $column['column_name'],
                        Str::camel($column['column_name']).'List',
                        $this->getLowercaseCaseClassName(),
                        Str::camel($column['column_name']),
                        $column['label_key'],
                        $column['value_key']
                    ];
                }
            } else {
                $new = [
                    $column['column_comment'],
                    $column['column_name'],
                    $column['column_name'].'List',
                    $this->getLowercaseCaseClassName(),
                    Str::camel($column['column_name']),
                    '',
                    ''
                ];
            }
            $searchTplType = $column['view_type'];
            if ($column['view_type'] == 'radio') {
                $searchTplType = 'select';
            }
            if  (empty($column['dict_type'])) {
                if (
                    $column['view_type'] == 'radio' ||
                    $column['view_type'] == 'select' ||
                    $column['view_type'] == 'checkbox' ) {
                    if (empty($column['model'])) {
                        $searchTplType = 'select2';
                    } else {
                        $searchTplType = 'select3';
                    }
                }
            } else {
                if ($column['view_type'] == 'radio') {
                    $searchTplType = 'select';
                }
            }
            if ($column['query_type'] == 'BETWEEN') {
                $searchTplType = $column['view_type'] == 'datetime' ? 'datetime' :  'rangeInput';
            }
            $tplPath = $this->getTplPath('search/' . $searchTplType);
            if (!file_exists($tplPath)) {
                continue;
            }
            $content .= $this->replaceFileText($old, $new, $tplPath) . PHP_EOL;
        }
        if (!empty($content)) {
            $content = substr($content, 0, -1);
        }
        return $this->setBlankSpace($content, '                    ');
    }

    /**
     * 搜索参数
     * @return string
     */
    public function getSearchParams()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!$column['is_search'] || $column['is_pk']) {
                continue;
            }
            if ($column['query_type'] == 'BETWEEN') {
                $content .= '"'.$column['column_name'].'":[],' . PHP_EOL;
            } else {
                $content .= '"'.$column['column_name'].'":"",' . PHP_EOL;
            }
        }
        if (!empty($content)) {
            $content = trim(trim($content), ',');
        }
        return $this->setBlankSpace($content, '      ');
    }

    /**
     * 获取列表内容
     * @return string
     */
    public function getTable()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!$column['is_lists'] || $column['column_name'] == 'site_id') {
                continue;
            }
            $old = [
                '{columnComment}',
                '{columnName}',
                '{lang}',
                '{dictType}'
            ];
            if (!empty($column['model'])) {
                $new = [
                    $column['column_comment'],
                    $column['column_name'].'_name',
                    Str::camel($column['column_name']),
                    $column['column_name'].'List'
                ];
            } else {
                $new = [
                    $column['column_comment'],
                    $column['column_name'],
                    Str::camel($column['column_name']),
                    $column['column_name'].'List'
                ];
            }
            $tplPath = $this->getTplPath('table/default');
            if ($column['view_type'] == 'imageSelect') {
                $tplPath = $this->getTplPath('table/Image');
            }

            if ($column['column_type'] == 'int' && $column['view_type'] == 'datetime') {
                $tplPath = $this->getTplPath('table/datetime');
            }
            if ($column['dict_type']) {
                $tplPath = $this->getTplPath('table/dictcolumn');
            }
            if (!file_exists($tplPath)) {
                continue;
            }
            $content .= $this->replaceFileText($old, $new, $tplPath) . PHP_EOL;
        }
        if (!empty($content)) {
            $content = substr($content, 0, -1);
        }
        return $this->setBlankSpace($content, '                    ');
    }

    /**
     * 获取查询条件
     * @return string
     */
    public function getQueryParams()
    {
        $content   = '';
        $queryDate = false;
        foreach ($this->tableColumn as $column) {
            if (!$column['is_pk']) {
                continue;
            }
            $content .= $column['column_name'] . ": ''," . PHP_EOL;
            if ($column['query_type'] == 'between' && $column['view_type'] == 'datetime') {
                $queryDate = true;
            }
        }
        if ($queryDate) {
            $content .= "start_time: ''," . PHP_EOL;
            $content .= "end_time: ''," . PHP_EOL;
        }
        $content = substr($content, 0, -2);
        return $this->setBlankSpace($content, '    ');
    }

    /**
     * 获取文件生成到模块的文件夹路径
     * @return string
     */
    public function getModuleOutDir()
    {
        if (!empty($this->addonName)) {
            $dir = dirname(app()->getRootPath()) . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR. $this->addonName  .DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR;
        } else {
            $dir = dirname(app()->getRootPath()) . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR;
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
        if (!empty($this->addonName)) {
            $dir = $this->outDir . 'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR;

        } else {
            $dir = $this->outDir . 'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR;
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
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR. $this->moduleName . DIRECTORY_SEPARATOR;
        } else {
            $dir = $this->rootDir . DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR . $this->moduleName . DIRECTORY_SEPARATOR;
        }
        $this->checkDir($dir);
        return $dir;
    }

    /**
     * 获取文件生成到插件中
     * @return string
     */
    public function getAddonObjectOutDir() {
        $dir = $this->rootDir . DIRECTORY_SEPARATOR.'server'.DIRECTORY_SEPARATOR.'addon'.DIRECTORY_SEPARATOR.$this->addonName.DIRECTORY_SEPARATOR.'admin'.DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR. $this->moduleName . DIRECTORY_SEPARATOR;
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
            $dir = 'addon/'.$this->addonName.'/admin/views/' . $this->moduleName . '/';

        } else {
            $dir = 'admin/app/views/' . $this->moduleName . '/';
        }
        return $dir;
    }

    /**
     * 生成的文件名
     * @return string
     */
    public function getFileName()
    {
        if ($this->className) return Str::lower($this->className).'.vue';
        return 'list.vue';
    }

    /**
     * 生成的API路径
     * @return string
     */
    public function getApiPath()
    {
        if (!empty($this->addonName)) {
            return 'addon/'.$this->addonName.'/api/'.$this->moduleName;
        } else {
            return 'app/api/'.$this->moduleName;
        }
    }

    /**
     * 获取字典内容
     * @return string
     */
    public function getDictDataContent()
    {
        $content = '';
        $isExist = [];
        foreach ($this->tableColumn as $column) {
            if (empty($column['dict_type']) || $column['is_pk']) {
                continue;
            }
            if (in_array($column['dict_type'], $isExist)) {
                continue;
            }
            $content  .= $column['dict_type'] . ': ' . "[]," . PHP_EOL;
            $isExist[] = $column['dict_type'];
        }
        if (!empty($content)) {
            $content = substr($content, 0, -1);
        }
        return $this->setBlankSpace($content, '    ');
    }

    /**
     * 调用字典方法
     * @return string
     */
    public function getDictList()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (empty($column['dict_type'])) {
                continue;
            }
            $content.= 'const '.$column['column_name'].'List = ref([] as any[])'.PHP_EOL.'const '.$column['column_name'].'DictList = async () => {'.PHP_EOL.$column['column_name'].'List.value = await (await useDictionary(' ."'".$column['dict_type']."'".')).data.dictionary'.PHP_EOL.'}'.PHP_EOL. $column['column_name'].'DictList();'.PHP_EOL;
        }
        if (!empty($content)) {
            $content = substr($content, 0, -1);
        }
        return $this->setBlankSpace($content, '    ');
    }

    /**
     * 增加关联方法
     * @return string
     */
    public function getWithApiPath()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str     = strripos($column['model'],'\\');
                $with    = Str::camel(substr($column['model'],$str+1));
                $content.= ' getWith'.Str::studly($with).'List,';
            }
        }
        return $content;
    }

    /**
     * 调用远程下拉方法
     * @return string
     */
    public function getModelData()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (empty($column['model'])) {
                continue;
            }
            $str     = strripos($column['model'],'\\');
            $with    = Str::camel(substr($column['model'],$str+1));
            $content.= PHP_EOL.'const '. Str::camel($column['column_name']).'List = ref([])'.PHP_EOL;
            $content.= 'const set'.Str::studly($column['column_name']).'List = async () => {'.PHP_EOL.Str::camel($column['column_name']).'List.value = await (await getWith'.Str::studly($with).'List({})).data' .PHP_EOL.'}'
                .PHP_EOL.'set'.Str::studly($column['column_name']).'List())';
        }
        if (!empty($content)) {
            $content = substr($content, 0, -1);
        }
        return $this->setBlankSpace($content, '    ');
    }

    /**
     * 编辑远程下拉方法
     * @return string
     */
    public function getEditWithApiPath()
    {
        $content = '';
        foreach ($this->tableColumn as $column) {
            if (!empty($column['model'])) {
                $str     = strripos($column['model'],'\\');
                $with    = Str::camel(substr($column['model'],$str+1));
                $content.= ' getWith'.Str::studly($with).'List,';
            }
        }
        return $content;
    }
}
