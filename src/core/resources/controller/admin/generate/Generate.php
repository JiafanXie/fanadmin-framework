<?php

namespace app\admin\controller\generate;


use app\Request;
use FanAdmin\base\BaseAdminController;
use FanAdmin\service\GenerateService;

/**
 * 一键生成curd控制器
 * Class Generate
 * @package app\admin\controller\curd
 */
class Generate extends BaseAdminController
{
    /**
     * 初始化
     */
    public function initialize()
    {
        parent::initialize(); // TODO: Change the autogenerated stub
        $this->service = new GenerateService();
    }

    /**
     * 代码生成器列表
     * @return \think\response\Json
     */
    public function lists(Request $request)
    {
        $data = $request->params([
            ['table_name', ''],
            ['table_content', ''],
            ['addon_name','']
        ]);
        return success($this->service->getList($data));
    }

    /**
     * 代码生成详情
     * @param int $id
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function info(int $id)
    {
        return success($this->service->getInfo($id));
    }

    /**
     * 代码预览
     * @param int $id
     * @return \think\response\Json
     */
    public function view(int $id)
    {
        return success($this->service->preview(['id' => $id]));
    }

    /**
     * 添加代码生成
     * @return \think\response\Json
     */
    public function add(Request $request)
    {
        $data = $request->params([
            ["table_name", ""],
        ], false);
        $this->validate($data, 'app\validate\generator\Generator.add');
        return success('添加成功', ['id' => $this->service->add($data)]);
    }

    /**
     * 代码生成编辑
     * @param $id
     * @return \think\response\Json
     */
    public function edit(int $id)
    {
        $data = $this->request->params([
            ["table_name", ""],
            ["table_content", ""],
            ["class_name", ""],
            ["module_name", ""],
            ['addon_name',''],
            ["edit_type", "1"],
            ["table_column", ""],
            ["is_delete",""],
            ['delete_column_name',''],
            ['order_type',"0"],
            ['order_column_name',''],
            ['parent_menu',''],
            ['relations',[]]
        ], false);

        $this->validate($data, 'app\validate\generator\Generator.edit');
        (new GenerateService())->edit($id, $data);
        return success('编辑成功');
    }

    /**
     * 代码生成删除
     * @param int $id
     * @return \think\response\Json
     */
    public function del(int $id)
    {
        if ($this->service->del($id)){
            return success('删除成功');
        }
        return error('删除失败');
    }

    /**
     * 生成代码
     * @return \think\response\Json
     */
    public function create(Request $request)
    {
        $data = $request->params([
            ['id', ''],
            ['generate_type', '2']
        ]);

        $data = (new GenerateService())->generate($data);
        return success('创建成功', $data);
    }

    /**
     * 获取数据表列表
     * @return \think\response\Json
     */
    public function tableList(Request $request)
    {
        $data = $request->params([
            ["name", ""],
            ["comment", ""],
        ]);
        $list = (new GenerateService())->tableList($data);
        return success('获取成功', $list);
    }

    /**
     * 代码生成检测
     * @return \think\response\Json
     */
    public function checkFile(Request $request)
    {
        $data = $request->params([
            ["id",'']
        ]);
        return success((new GenerateService())->checkFile($data));

    }

    /**
     * 获取表字段
     * @return \think\response\Json
     */
    public function getTableColumn(Request $request)
    {
        $data = $request->params([
            ["table_name", ""],
        ]);
        return success((new GenerateService())->getTableColumn($data));
    }

    /**
     * 获取全部模型
     * @return \think\response\Json
     */
    public function getModels(Request $request)
    {
        $data = $request->params([
            ["addon","system"]
        ]);
        return success((new GenerateService())->getModels($data));
    }

    /**
     * 根据模型获取表字段
     * @return \think\response\Json
     */
    public function getModelTableColumn(Request $request)
    {
        $data = $request->params([
            ["model",""]
        ]);
        return success((new GenerateService())->getModelColumn($data));
    }
}