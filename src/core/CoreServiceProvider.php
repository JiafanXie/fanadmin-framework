<?php

namespace FanAdmin;


use FanAdmin\middleware\AdminTokenMiddleware;
use FanAdmin\src\http\middleware\Cors;
use think\Service;
use think\facade\Route;


/**
 * Class CoreServiceProvider
 * @package FanAdmin
 */
class CoreServiceProvider extends Service
{

    /**
     * @var string[] 注册自定义服务
     */
    public $bind = [
        'auth' => Auth::class,       // 用户认证服务
    ];

    /**
     * 服务执行
     */
    public function boot()
    {
        
    }

    /**
     * 服务注册
     */
    public function register()
    {
        // 插件服务
        $this->registerAddonService();

        // admin路由
        $this->registerAdminRoute();

        // 插件路由
        $this->registerAddonRoute();

        // 中间件
        $this->registerMiddleware();

        // 插件中间件
        $this->registerAddonMiddleware();

        // 事件
        $this->registerEvent();

        // 插件事件
        $this->registerAddonEvent();

        // command命令
        $this->commands([

        ]);
        // 插件command命令
        $this->commands([

        ]);
    }

    /**
     * 注册插件服务
     */
    protected function registerAddonService()
    {
        foreach(Admin::getAddonService() as $service){
            $this->app->register($service);
        }
    }

    /**
     * 注册admin路由
     */
    public function registerAdminRoute()
    {
        (new Admin())->loadAdminRoute();
    }

    /**
     * 注册插件路由
     */
    protected function registerAddonRoute()
    {
        $this->app->request->setRoot('/' . 'addon');
        foreach(Admin::getAddonRoute() as $path){
            Route::group('/addon',function()use($path){
                $this->loadRoutesFrom($path);
                // include $path;
            });
        }
    }

    /**
     * 注册中间件
     */
    protected function registerMiddleware()
    {
        $middleware = $this->app->config->get('middleware');
        $middleware['alias']['auth'] = [
            AdminTokenMiddleware::class,
        ];
        $middleware['alias']['cors'] = [
            Cors::class,
        ];
        $this->app->config->set($middleware, 'middleware');
    }

    /**
     * 注册插件中间件
     */
    protected function registerAddonMiddleware()
    {

    }

    /**
     * 注册事件
     */
    public function registerEvent()
    {

    }

    /**
     * 注册插件事件
     */
    public function registerAddonEvent()
    {

    }
}