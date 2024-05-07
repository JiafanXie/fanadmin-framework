<?php

<{namespace}>

use app\service\admin\BaseAdminService;
<{use}>
/**
 * <{notes}>服务层
 * Class <{className}>Service
 * @package <{package}>
 */
class <{className}>Service extends BaseAdminService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new <{className}>();
    }

    /**
     * <{notes}>列表
     * @param array $where
     * @return array
     */
    public function getList(array $where = [])
    {
        $field = '<{fields}>';
        $order = '<{order}>';
        $searchModel = <{searchModel}>
        $list = $this->pageQuery($searchModel);
        return $list;
    }

    /**
     * <{notes}>详情
     * @param int $id (<{notes}>id)
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = '<{fields}>';
        $info = <{infoSearchModel}>
        return $info;
    }

    /**
     * <{notes}>添加
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $res = $this->model->create($data);
        return $res-><{pk}>;
    }

    /**
     * <{notes}>编辑
     * @param int $id (<{notes}>id)
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $this->model->where([
            ['<{pk}>', '=', $id]
        ])->update($data);
        return true;
    }

    /**
     * <{notes}>删除 支持批量删除
     * @param int $ids (<{notes}>ids)
     * @return bool
     */
    public function delete(int $ids)
    {
        $ids = explode(',', $ids);
        $res = $this->model->where([
            ['<{pk}>', 'in', $ids]
        ])->delete();
        return $res;
    }

    <{with}>

}
