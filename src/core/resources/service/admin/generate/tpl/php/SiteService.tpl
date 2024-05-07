<?php

<{namespace}>

use app\service\site\BaseAdminApiService;
<{use}>
/**
 * <{notes}>服务层
 * Class <{className}>Service
 * @package <{package}>
 */
class <{className}>Service extends BaseAdminApiService
{
    public function __construct()
    {
        parent::__construct();
        $this->model = new <{className}>}();
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
     * @param int $id
     * @return array
     */
    public function getInfo(int $id)
    {
        $field = '<{fields}>';
        $info = <{searchModel}>
        return $info;
    }

    /**
     * <{notes}>添加
     * @param array $data
     * @return mixed
     */
    public function add(array $data)
    {
        $data['site_id'] = $this->site_id;
        $res = $this->model->create($data);
        return $res-><{pk}>;
    }

    /**
     * <{notes}>编辑
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function edit(int $id, array $data)
    {
        $this->model->where([
            ['<{pk}>', '=', $id],
            ['site_id', '=', $this->site_id]
        ])->update($data);
        return true;
    }

    /**
     * <{notes}>删除 支持批量删除
     * @param int $id (<{notes}>ids)
     * @return bool
     */
    public function delete(int $ids)
    {
        $res = $this->model->where([
            ['<{pk}>', 'in', $ids],
            ['site_id', '=', $this->site_id]
        ])->delete();
        return $res;
    }

    <{with}>

}
