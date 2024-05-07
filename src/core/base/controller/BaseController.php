<?php
declare (strict_types=1);

namespace FanAdmin\base\controller;


use app\Request;
use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 * Class BaseController
 * @package core\base\controller
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var Request
     */
    protected $request;

    /**
     * 应用实例
     * @var App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 服务层
     * @var
     */
    protected $service;

    /**
     * 构造方法
     * @access public
     * @param App $app 应用对象
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        $this->request = $this->app->request;
        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
    }

    /**
     * 验证数据
     * @access protected
     * @param array $data 数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array $message 提示信息
     * @param bool $batch 是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = str_contains($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch();
        }

        return $v->failException()->check($data);
    }

    /**
     * 批量操作
     * @param $items
     * @param \Closure|null $callback
     * @return bool
     * @throws \core\exception\FanException
     */
    public function batchHander ($items, \Closure $callback = null) {
        $count = \think\facade\Db::transaction (
            function () use ($items, $callback) {
                $count = 0;
                foreach ($items as $item) {
                    if ($callback) {
                        $count += $callback($item);
                    } else {
                        $count += $item->delete();
                    }
                }
                return $count;
            });
        if ($count) {
            return true;
        } else {
            throw new \core\exception\FanException('未操作任何行');
        }
    }

    /**
     * 参数验证操作
     * @param array $params
     * @param string $validator
     * @return mixed|void
     */
    protected function validatesHander (array $params, string $validator = "") {
        if (false !== strpos($validator, '.')) {
            // 是否支持场景验证
            [$validator, $scene] = explode('.', $validator);
        }
        // 获取validate实例
        $class = false !== strpos($validator, '\\') ? $validator : str_replace("controller", "validate", get_class($this));
        if (!class_exists($class)) {
            return;
        }
        $validate     = new $class();
        // 添加场景验证
        if (!empty($scene)) {
            $validate->scene($scene);
        } else {
            // halt(22);
            // 只验证传入参数
            $validate->only(array_keys($params));
        }
        // 失败自动抛出异常信息
        return $validate->failException(true)->check($params);
    }

    /**
     * 过滤前端发来的短时间内的重复的请求
     * @param null $key
     * @param int $expire
     */
    public function repeatFilter ($key = null, $expire = 5) {
        if (!$key) {
            $httpName = app('http')->getName();
            $url = request()->baseUrl();
            $ip = request()->ip();

            $key = $httpName . ':' . $url . ':' . $ip;
        }
        if (cache()->store('persistent')->has($key)) {
            throw new \core\exception\FanException('请稍后再试');
        }
        // 缓存 5 秒
        cache()->store('persistent')->tag('repeat_filter')->set($key, time(), $expire);
    }

    /**
     * 监听数据库 sql
     */
    public function dbListen () {
        \think\facade\Db::listen(function ($sql, $time) {
            echo $sql . '<br/>' . $time;
        });
    }

    /**
     * 取请求的 access
     * @return string
     */
    public function accessName () {
        $root       = substr(request()->root(), 1);
        $controller = request()->controller();
        $action = request()->action();
        $access = strtolower("{$root}.{$controller}.{$action}");
        return $access;
    }

}
