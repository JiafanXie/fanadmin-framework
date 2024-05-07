<?php

use think\facade\Route;


CONST PLUGIN_BASE_PATH = "FanAdmin\src\http\controller\admin\addon";
/**
 * 应用插件相关路由
 */
Route::group('addon', function () {
    // 已安装插件
    Route::get('local', PLUGIN_BASE_PATH.'\Addon@getLocalAddonList');
    // 安装插件
    Route::post('install/:addon', PLUGIN_BASE_PATH.'\Addon/install');
    // 云安装插件
    Route::post('cloud-install/:addon', PLUGIN_BASE_PATH.'\Addon/cloudInstall');
    // 云编译进度
    Route::get('cloud-install/:addon', PLUGIN_BASE_PATH.'\Addon/cloudInstallLog');
    // 插件安装检测环境
    Route::get('install-check/:addon', PLUGIN_BASE_PATH.'\Addon/installCheck');
    // 安装任务
    Route::get('install-task', PLUGIN_BASE_PATH.'\Addon/getInstallTask');
    // 下载插件
    Route::get('download/:addon', PLUGIN_BASE_PATH.'\Addon/download');

    // 卸载插件环境检测
    Route::get('uninstall-check/:addon', PLUGIN_BASE_PATH.'\Addon/uninstallCheck');
    // 卸载插件
    Route::delete('uninstall/:addon', PLUGIN_BASE_PATH.'\Addon/uninstall');
    // 应用列表(...)
    Route::get('app-list', PLUGIN_BASE_PATH.'\App/getAppList');
    // 已安装有效应用
    Route::get('addon-list', PLUGIN_BASE_PATH.'\Addon/getAddonList');
    // 取消安装任务
    Route::put('cancel/:addon', PLUGIN_BASE_PATH.'\Addon/cancleInstall');
});

/**
 * 开发插件相关路由
 */
Route::group('addon-dev', function () {
    // 开发插件类型
    Route::get('type', PLUGIN_BASE_PATH.'\AddonDevelop/type');
    // 开发插件列表
    Route::get('addon', PLUGIN_BASE_PATH.'\AddonDevelop/lists');
    // 开发插件查询
    Route::get('addon/:key', PLUGIN_BASE_PATH.'\AddonDevelop/info');
    // 新增开发插件
    Route::post('addon/:key', PLUGIN_BASE_PATH.'\AddonDevelop/add');
    // 编辑开发插件
    Route::put('addon/:key', PLUGIN_BASE_PATH.'\AddonDevelop/edit');
    // 删除开发插件
    Route::delete('addon/:key', PLUGIN_BASE_PATH.'\AddonDevelop/del');
    // 校验开发插件是否存在
    Route::get('addon-check/:key', PLUGIN_BASE_PATH.'\AddonDevelop/checkKey');
    // 打包开发插件
    Route::post('addon-build/:key', PLUGIN_BASE_PATH.'\AddonDevelop/build');
    // 下载开发插件
    Route::post('addon-download/:key', PLUGIN_BASE_PATH.'\AddonDevelop/download');
});