<?php

namespace FanAdmin\base\service;


use think\db\exception\DbException;
use think\Model;

/**
 * 基础服务层
 * Class BaseService
 * @package FanAdmin\base\service
 */
abstract class BaseService
{
    /**
     * @var $model
     */
    protected $model;

    /**
     * @var \think\facade\Request|\think\Request
     */
    protected $request;

    public function __construct()
    {
        $this->request = request();
    }

    /**
     * 分页列表参数(页码和每页多少条)
     * @return mixed
     */
    public function getPageParam()
    {
        $page = request()->params([
            ['page', 1],
            ['limit', 15]
        ]);
        return $page;
    }

    /**
     * 分页列表
     * @param Model $model
     * @param array $where
     * @param string $field
     * @param string $order
     * @param array $append
     * @return array
     * @throws DbException
     */
    public function getPageList(
        Model $model,
        array $where,
        string $field = '*',
        string $order = '',
        array $append = [],
        $with = null,
        $each = null
    )
    {
        $page_params = $this->getPageParam();
        $page  = $page_params['page'];
        $limit = $page_params['limit'];

        $list = $model->where($where)
            ->when(
                $append,
                function ($query) use ($append) {
                    $query->append($append);
                })
            ->when(
                $with,
                function ($query) use ($with) {
                    $query->with($with);
                })
            ->field($field)
            ->order($order)
            ->paginate([
                'list_rows' => $limit,
                'page'      => $page,
            ]);
        if (!empty($each)) {
            $list = $list->each($each);
        }
        return $list->toArray();
    }

    /**
     * 分页数据查询，传入model（查询后结果）
     * @param $model BaseModel
     * @return array
     * @throws DbException
     */
    public function pageQuery(
        $model,
        $each = null
    )
    {
        $page_params = $this->getPageParam();
        $page  = $page_params['page'];
        $limit = $page_params['limit'];
        $list  = $model->paginate([
            'list_rows' => $limit,
            'page' => $page,
        ]);
        if (!empty($each)) {
            $list = $list->each($each);
        }
        return $list->toArray();
    }

    /**
     * 分页视图列表查询
     * @param Model $model
     * @param array $where
     * @param string $field
     * @param string $order
     * @param array $append
     * @return array
     * @throws DbException
     */
    public function getPageViewList(
        Model $model,
        array $where,
        string $field = '*',
        string $order = '',
        array $append = [],
        $with = null,
        $each = null
    )
    {
        $page_params = $this->getPageParam();
        $page = $page_params['page'];
        $limit = $page_params['limit'];

        $list = $model->where($where)
            ->when($append,
                function ($query) use ($append) {
                    $query->append($append);
            })
            ->when($with,
                function ($query) use ($with) {
                    $query->withJoin($with);
            })
            ->field($field)
            ->order($order)
            ->paginate([
                'list_rows' => $limit,
                'page'      => $page,
            ]);
        if (!empty($each)) {
            $list = $list->each($each);
        }
        return $list->toArray();
    }
}