<?php

namespace FanAdmin\lib\generate\service;


use FanAdmin\base\BaseAdminService;
use FanAdmin\exception\FanException;
use FanAdmin\lib\generate\Generate;
use FanAdmin\model\generate\AddonModel;
use FanAdmin\model\generate\GenerateColumnModel;
use FanAdmin\model\generate\GenerateTableModel;
use Exception;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use think\db\exception\ModelNotFoundException;
use think\facade\Db;
use think\helper\Str;


/**
 * 代码生成器服务
 * Class GenerateService
 * @package FanAdmin\service\generate
 */
class GenerateService extends BaseAdminService
{
    /**
     * @var GenerateTableModel
     */
    protected $generateTableModel;

    /**
     * @var GenerateColumnModel
     */
    protected $generateTableColumModel;

    /**
     * @var AddonModel
     */
    protected $addonModel;

    function __construct()
    {
        parent::__construct();
        $this->generateTableModel = new GenerateTableModel();
        $this->generateTableColumModel = new GenerateColumnModel();
        $this->addonModel = new AddonModel();
    }

    /**
     * 获取代码生成列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        $field = 'id,table_name,table_content,class_name,edit_type,create_time,addon_name';
        $order = 'create_time desc';
        $search_model = $this->generateTableModel->withSearch(['table_name', 'table_content','addon_name'], $where)->with('addon')->field($field)->order($order);
        return $this->pageQuery($search_model);
    }

    /**
     * 获取代码生成信息
     * @param int $id
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     * @throws ModelNotFoundException
     */
    public function getInfo(int $id)
    {
        // 字段
        $field = 'id,
        table_name,
        table_content,
        class_name,
        module_name,
        edit_type,
        addon_name,
        order_type,
        parent_menu,
        relations,
        synchronous_number';
        // 获取生成表信息
        $info = $this->generateTableModel->field($field)
            ->where([['id', '=', $id]])
            ->findOrEmpty()
            ->toArray();
        // 获取生成表字段信息
        $info['table_column'] = $this->generateTableColumModel->where([
            ['table_id', '=', $id]
        ])->select()->toArray();
        // 生成表字段软删除字段
        $column = $this->generateTableColumModel->where([
            ['table_id', '=', $id],
            ['is_delete', '=', 1]
        ])->find();
        if($info && $info['order_type'] != 0)
        {
            // 有排序
            // 查询排序字段
            $orderColumn = $this->generateTableColumModel->where([
                ['table_id', '=', $id],
                ['is_order','=',1]
            ])->find();
            if($orderColumn)
            {
                $info['order_column_name'] = $orderColumn['column_name'];
            }else{
                $info['order_column_name'] = '';
            }
        }else{
            $info['order_column_name'] = '';
        }

        if($column)
        {
            // 有软删除字段
            $info['is_delete'] = 1;
            $info['delete_column_name'] = $column['column_name'];
        }else{
            // 没有软删除字段
            $info['is_delete'] = 0;
            $info['delete_column_name'] = '';
        }
        if($info['relations'] == '[]')
        {
            // 没有表关联信息
            $info['relations'] = [];
        }else{
            // 有表关联信息
            if(!empty($info['relations']))
            {
                $info['relations'] = json_decode($info['relations'],true);
            }else{
                $info['relations'] = [];
            }

        }
        if($info && !empty($info['table_column']))
        {
            // 遍历表字段
            foreach ($info['table_column'] as &$value)
            {
                if($value['view_type'] === 'number')
                {
                    //

                    if(!empty($value['validate_type']))
                    {
                        // 验证方式

                        $num_validate = json_decode($value['validate_type'],true);
                        if($num_validate[0] == 'between')
                        {
                            $value['view_max'] = $num_validate[1][1];
                            $value['view_min'] = $num_validate[1][0];
                        } else if($num_validate[0] == 'max')
                        {
                            $value['view_max'] = $num_validate[1][0];

                        } else if($num_validate[0] == 'min')
                        {
                            $value['view_min'] = $num_validate[1][0];
                        }else{
                            $value['view_max'] = 100;
                            $value['view_min'] = 0;
                        }
                    }else{
                        $value['view_min'] = 0;
                        $value['view_max'] = 100;
                    }

                }else{
                    $value['view_min'] = 0;
                    $value['view_max'] = 100;
                }

                if(!empty($value['validate_type']))
                {
                    $validate = json_decode($value['validate_type'],true);

                    if($validate[0] == 'between')
                    {
                        $value['max_number'] = $validate[1][1];
                        $value['min_number'] = $validate[1][0];
                    } else if($validate[0] == 'max')
                    {
                        $value['max_number'] = $validate[1][0];

                    } else if($validate[0] == 'min')
                    {
                        $value['min_number'] = $validate[1][0];
                    }else{
                        $value['max_number'] = 120;
                        $value['min_number'] = 1;
                    }
                    $value['validate_type'] = $validate[0];
                }else{
                    $value['max_number'] = 120;
                    $value['min_number'] = 1;
                }
                if(!empty($value['model']))
                {
                    $value['select_type'] = 2;
                }else{
                    $value['select_type'] = 1;
                }

            }
        }
        return $info;
    }

    /**
     * 添加代码生成
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        Db::startTrans();
        try {
            $sql         = 'SHOW TABLE STATUS WHERE 1=1 ';
            $tablePrefix = config('database.connections.mysql.prefix');
            if (!empty($data['table_name'])) {
                $sql .= "AND name='" . $data['table_name']."'";
            }
            $tables = Db::query($sql);
            $tableInfo = $tables[0] ?? [];
            if(empty($tableInfo)) throw new FanException($data['table_name'].'不存在');
            $tableName = str_replace($tablePrefix, '', $tableInfo['Name']);
            $fields = Db::name($tableName)->getFields();
            $saveTableData = [
                'table_name'    => $tableName,
                'table_content' => $tableInfo['Comment'],
                'class_name'    => $tableName,
                'create_time'   => time(),
                'module_name'   => $tableName
            ];
            $res = $this->generateTableModel->create($saveTableData);
            $tableId = $res->id;
            $saveColumnData = [];
            $defaultColumn = ['id', 'create_time', 'update_time'];
            foreach ($fields as $k => $v){
                $required = 0;
                if ($v['notnull'] && !$v['primary'] && !in_array($v['name'], $defaultColumn)) {
                    $required = 1;
                }
                $saveColumnData[] = [
                    'table_id'       => $tableId,
                    'column_name'    => $v['name'],
                    'column_comment' => $v['comment'],
                    'column_type'    => self::getDbFieldType($v['type']),
                    'is_required'    => $required,
                    'is_pk'          => $v['primary'] ? 1 : 0,
                    'is_insert'      => !in_array($v['name'], $defaultColumn) ? 1 : 0,
                    'is_update'      => !in_array($v['name'], $defaultColumn) ? 1 : 0,
                    'is_lists'       => !in_array($v['name'], $defaultColumn) ? 1 : 0,
                    'is_delete'      => 0,
//                    'is_query' => !in_array($v['name'], $default_column) ? 1 : 0,
                    'query_type'     => '=',
                    'view_type'      => 'input',
                    'dict_type'      => $v['dict_type'] ?? '',
                    'addon'          => $v['addon'] ?? '',
                    'model'          => $v['model'] ?? '',
                    'label_key'      => $v['label_key'] ?? '',
                    'value_key'      => $v['value_key'] ?? '',
                    'create_time'    => time(),
                    'update_time'    => time()
                ];
            }
            $this->generateTableColumModel->saveAll($saveColumnData);
            Db::commit();
            return $tableId;
        } catch ( Exception $e) {
            Db::rollback();
            throw new FanException($e->getMessage());
        }

    }

    /**
     * 代码生成编辑
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function edit(int $id, array $params)
    {
        Db::startTrans();
        try {
            // 更新主表信息
            $this->generateTableModel->where([ ['id', '=', $id] ])->save([
                'id' => $id,
                'table_name'    => $params['table_name'],
                'table_content' => $params['table_content'],
                'module_name'   => $params['module_name'] ?? '',
                'class_name'    => $params['class_name'] ?? '',
                'edit_type'     => $params['edit_type'] ?? 1,
                'addon_name'    => $params['addon_name'] ?? '',
                'order_type'    => $params['order_type'] ?? 0,
                'parent_menu'   => $params['parent_menu'] ?? '',
                'relations'     => $params['relations'] ?? []
            ]);
            // 删除生成表字段信息
            $this->generateTableColumModel->where([['table_id', '=', $id]])->delete();

            // 生成表字段信息转成数组
            $params['table_column'] = json_decode($params['table_column'], true);

            // 更新从表字段信息
            $saveColumnData = [];
            foreach ($params['table_column'] as $item) {
                if($params['is_delete'] == 1)
                {
                    // 软删除字段
                    if($item['column_name'] == $params['delete_column_name'])
                    {
                        $item['is_delete'] = 1;
                    }else{
                        $item['is_delete'] = 0;
                    }
                }else{
                    $item['is_delete'] = 0;
                }

                if($params['order_type'] != 0)
                {
                    // 排序字段
                    if($item['column_name'] == $params['order_column_name'])
                    {
                        $item['is_order'] = 1;
                    }else{
                        $item['is_order'] = 0;
                    }
                }else{
                    $item['is_order'] = 0;
                }
                if(!empty($item['validate_type']) && $item['view_type'] != 'number')
                {
                    // 验证方式
                    if($item['validate_type'] == 'between')
                    {
                        $validate_type = [$item['validate_type'],[$item['min_number'],$item['max_number']]];
                    }else if($item['validate_type'] == 'max'){
                        $validate_type = [$item['validate_type'],[$item['max_number']]];
                    }else if($item['validate_type'] == 'min'){
                        $validate_type = [$item['validate_type'],[$item['min_number']]];
                    }else{
                        $validate_type = [$item['validate_type'],[]];
                    }
                    $item['validate_type'] = json_encode($validate_type,JSON_UNESCAPED_UNICODE);
                }
                if($item['view_type'] === 'number')
                {
                    $validate_type = ['between',[$item['view_min'],$item['view_max']]];
                    $item['validate_type'] = $validate_type;
                    $item['validate_type'] = json_encode($validate_type,JSON_UNESCAPED_UNICODE);
                }
                if(!empty($item['model']))
                {
                    $item['dict_type'] = '';
                }

                // 生成表字段保存数据
                $saveColumnData[] = [
                    'table_id'       => $id,
                    'column_name'    => $item['column_name'] ?? '',
                    'column_comment' => $item['column_comment'] ?? '',
                    'is_pk'          => $item['is_pk'],
                    'is_required'    => $item['is_required'] ?? 0,
                    'is_insert'      => $item['is_insert'] ?? 0,
                    'is_update'      => $item['is_update'] ?? 0,
                    'is_lists'       => $item['is_lists'] ?? 0,
//                    'is_query' => $item['is_query'] ?? 0,
                    'is_search'      => $item['is_search'] ?? 0,
                    'is_delete'      => $item['is_delete'] ?? 0,
                    'is_order'       => $item['is_order'] ?? 0,
                    'query_type'     => $item['query_type'],
                    'view_type'      => $item['view_type'] ?? 'input',
                    'dict_type'      => $item['dict_type'] ?? '',
                    'addon'          => $item['addon'] ?? '',
                    'model'          => $item['model'] ?? '',
                    'label_key'      => $item['label_key'] ?? '',
                    'value_key'      => $item['value_key'] ?? '',
                    'update_time'    => time(),
                    'create_time'    => time(),
                    'column_type'    => $item['column_type'] ?? 'string',
                    'validate_type'  => $item['validate_type'] ?? '',
                    'validate_rule'  => $params['rule'] ?? []
                ];
            }
            // 保存
            $this->generateTableColumModel->saveAll($saveColumnData);
            Db::commit();
            return true;
        } catch ( Exception $e) {
            Db::rollback();
            throw new FanException($e->getMessage());
        }
    }

    /**
     * 删除代码生成
     * @param int $id
     * @return bool
     */
    public function delete(int $id)
    {
        Db::startTrans();
        try {
            // 删除生成表信息
            $this->generateTableModel->where([['id', '=', $id]])->delete();
            // 删除生成表字段表 信息
            $this->generateTableColumModel->where([['table_id', '=', $id]])->delete();
            Db::commit();
            return true;
        } catch ( Exception $e) {
            Db::rollback();
            throw new FanException($e->getMessage());
        }
    }

    /**
     * 生成代码
     * @param array $params
     * @return array|string[]
     */
    public function generate(array $params)
    {
        if($params['generate_type'] == 2)
        {
            // 下载
            try {
                $id = $params['id'];
                $tableInfo = $this->generateTableModel->where([
                    ['id', '=', $id]
                ])->field('*')->find()->toArray();
                $tableInfo['fields'] = $this->generateTableColumModel->where([
                    ['table_id', '=', $id]
                ])->field('*')->select()->toArray();

                $generator = new Generate();
                $generator->delOutFiles();

                $flag = array_unique(array_column($tableInfo, 'table_name'));
                $flag = implode(',', $flag);
                $generator->setFlag(md5($flag . time()));
                $tableInfo['generate_type'] = 2;
                $generator->generate($tableInfo);

                $zipFile = '';
                // 生成压缩文件包
                if ($generator->getFlag()) {
                    $generator->zipFile();
                    $generator->delFlag();
                    $zipFile = $generator->getDownloadUrl();
                }

                return ['file' => $zipFile];

            } catch ( Exception $e) {
                throw new FanException($e->getMessage());
            }
        }else if($params['generate_type'] == 3){
            // 同步
            try {
                $id = $params['id'];
                $tableInfo = $this->generateTableModel->where([
                    ['id', '=', $id]
                ])->field('*')->find()->toArray();
                $tableInfo['fields'] = $this->generateTableColumModel->where([
                    ['table_id', '=', $id]
                ])->field('*')->select()->toArray();
                $synchronous_number = $tableInfo['synchronous_number'] +1;
                $this->generateTableModel->where([
                    ['id', '=', $id]
                ])->save([
                    'synchronous_number' => $synchronous_number
                ]);
                $generator = new Generate();
                $generator->delOutFiles();
                $flag = array_unique(array_column($tableInfo, 'table_name'));
                $flag = implode(',', $flag);
                $generator->setFlag(md5($flag . time()));
                $tableInfo['generate_type'] = 3;
                $generator->generate($tableInfo);
                return [];
            } catch ( Exception $e) {
                throw new FanException($e->getMessage());
            }
        }else{
            return [];
        }
    }

    /**
     * 预览
     * @param array $params
     * @return array
     */
    public function preview(array $params)
    {
        try {
            $id = $params['id'];
            // 查询生生成表信息
            $tableInfo = $this->generateTableModel->where([
                ['id', '=', $id]
            ])->field('*')->find()->toArray();
            // 查询生成表字段表信息
            $tableInfo['fields'] = $this->generateTableColumModel->where([
                ['table_id', '=', $id]
            ])->field('*')->select()->toArray();
            // 生成预览信息
            $generator = new Generate();
            $tableInfo['generate_type'] = 1;
            return $generator->preview($tableInfo);
        } catch ( Exception $e) {
            throw new FanException($e->getMessage());
        }
    }

    /**
     * 获取数据表字段类型
     * @param string $type
     * @return string
     */
    public static function getDbFieldType(string $type): string
    {
        if (str_starts_with($type, 'set') || str_starts_with($type, 'dict')) {
            $result = 'string';
        } elseif (preg_match('/(double|float|decimal|real|numeric)/is', $type)) {
            $result = 'float';
        } elseif (preg_match('/(int|serial|bit)/is', $type)) {
            $result = 'int';
        } elseif (preg_match('/bool/is', $type)) {
            $result = 'bool';
        } elseif (str_starts_with($type, 'timestamp')) {
            $result = 'timestamp';
        } elseif (str_starts_with($type, 'datetime')) {
            $result = 'datetime';
        } elseif (str_starts_with($type, 'date')) {
            $result = 'date';
        } else {
            $result = 'string';
        }
        return $result;
    }

    /**
     * 查询表
     * @param array $params
     * @return mixed
     */
    public function tableList(array $params = [])
    {
        // sql语句
        $sql = 'SHOW TABLE STATUS WHERE 1=1 ';
        if (!empty($params['name'])) {
            $sql .= "AND name LIKE '%" . $params['name'] . "%'";
        }
        if (!empty($params['comment'])) {
            $sql .= "AND comment LIKE '%" . $params['comment'] . "%'";
        }
        // 返回查询语句
        return Db::query($sql);
    }

    /**
     * 检测文件是否存在
     * @param $checkFile
     * @return int
     */
    public function checkFile($checkFile)
    {
        // 获取生成表信息
        $info = $this->generateTableModel->where([
            ['id', '=', $checkFile['id']]
        ])->findOrEmpty()->toArray();
        // 文件目录
        $dir = dirname(root_path());
        if(empty($info['class_name']))
        {
            // 类名称
            $info['class_name'] =  Str::studly($info['table_name']);
        }
        if(empty($info['module_name']))
        {
            // 模块名称
            $info['module_name'] = Str::camel($info['table_name']);
        }
        if(!empty($info['addon_name']))
        {
            // 插件
            $controllerFile = $dir.'\\server\\addon\\'.$info['addon_name'].'\\app\\admin\\controller\\'.$info['module_name'].'\\'.$info['class_name'].'.php';
            $modelFile      = $dir.'\\server\\addon\\'.$info['addon_name'].'\\app\\model\\'.$info['module_name'].'\\'.$info['class_name'].'.php';
            $validateFile   = $dir.'\\server\\addon\\'.$info['addon_name'].'\\app\\validate\\'.$info['module_name'].'\\'.$info['class_name'].'.php';
            $webViewFile    = $dir.'\\admin\\src\\'.$info['addon_name'].'\\views\\'.$info['module_name'];
        }else{
            // 应用
            $controllerFile = $dir.'\\server\\app\\admin\\controller\\'.$info['module_name'].'\\'.$info['class_name'].'.php';
            $modelFile      = $dir.'\\server\\app\model\\'.$info['module_name'].'\\'.$info['class_name'].'.php';
            $validateFile   = $dir.'\\server\\app\validate\\'.$info['module_name'].'\\'.$info['class_name'].'.php';
            $webViewFile    = $dir.'\\admin\\src\\views'.'\\'.$info['module_name'];
        }
        if(file_exists($controllerFile) && file_exists($modelFile) && file_exists($validateFile)  && file_exists($webViewFile))
        {
            $isCheck = 1;
        }else{
            $isCheck = 2;
        }
        return $isCheck;
    }

    /**
     * 获取表字段
     * @param $data
     * @return array
     */
    public function getTableColumn($data)
    {
        // sql语句
        $sql = 'SHOW TABLE STATUS WHERE 1=1 ';
        $tablePrefix = config('database.connections.mysql.prefix');
        if (!empty($data['table_name'])) {
            $sql .= "AND name='" .$tablePrefix.$data['table_name']."'";
        }
        // 表信息
        $tables = Db::query($sql);
        $tableInfo = $tables[0] ?? [];
        if(empty($tableInfo)) throw new FanException($data['table_name'].'不存在');
        $tableName = str_replace($tablePrefix, '', $tableInfo['Name']);
        // 返回表字段信息
        return Db::name($tableName)->getFields();
    }

    /**
     * 获取所有模型
     * @param $data
     * @return array
     */
    public static function getModels($data)
    {
        if($data['addon'] == 'system')
        {
            // 获取系统模型
            $modulePath = dirname(root_path()) . '/serve/app/model/';
            if(!is_dir($modulePath)) {
                return [];
            }
            $modulefiles = glob($modulePath . '*');
            $targetFiles = [];
            foreach ($modulefiles as $file) {
                $fileBaseName = basename($file, '.php');
                if (is_dir($file)) {
                    $file = glob($file . '/*');
                    foreach ($file as $item) {
                        if (is_dir($item)) {
                            continue;
                        }
                        $targetFiles[] = sprintf(
                            "app\\model\\%s\\%s",
                            $fileBaseName,
                            basename($item, '.php')
                        );
                    }
                } else {
                    if ($fileBaseName == 'BaseModel') {
                        continue;
                    }
                    $targetFiles[] = sprintf(
                        "app\\model\\%s",
                        basename($file, '.php')
                    );
                }
            }
        }else{
            //  获取插件模型
            $path = dirname(root_path())."/serve/addon/".$data['addon']."/app/model";
            if(!is_dir($path)) {
                return [];
            }
            $modulefiles = glob($path . '/*');
            $targetFiles = [];
            foreach ($modulefiles as $file) {
                $fileBaseName = basename($file, '.php');
                if (is_dir($file)) {
                    $file = glob($file . '/*');
                    foreach ($file as $item) {
                        if (is_dir($item)) {
                            continue;
                        }
                        $targetFiles[] = sprintf(
                            'addon\\'.$data['addon']."\\app\\model\\%s\\%s",
                            $fileBaseName,
                            basename($item, '.php')
                        );
                    }
                } else {
                    if ($fileBaseName == 'BaseModel') {
                        continue;
                    }
                    $targetFiles[] = sprintf(
                        'addon\\'.$data['addon']."\\app\\model\\%s",
                        basename($file, '.php')
                    );
                }
            }
        }
        return $targetFiles;
    }

    /**
     * 获取模型所有字段
     * @param $data
     * @return mixed
     */
    public function getModelColumn($data)
    {
        // 获取表字段信息
        $model = new $data['model'];
        $table = $model->getModelColumn();
        return $table;
    }
}
