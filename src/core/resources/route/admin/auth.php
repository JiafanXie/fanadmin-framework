<?php

use think\facade\Route;


CONST AUTH_BASE_PATH = "FanAdmin\src\http\controller\admin\auth";
/**
 * 权限相关路由
 */
Route::group('auth',function(){
    // 获取用户信息
    Route::get('user-info',AUTH_BASE_PATH.'\Auth@info');
    // 用户菜单
    Route::get('user-menu',AUTH_BASE_PATH.'\Auth@userPermissions');

    // 菜单列表
    Route::get('menu',AUTH_BASE_PATH.'\Permissions@list');
    // 菜单详情
    Route::get('menu-info/:id',AUTH_BASE_PATH.'\Permissions@info');
    // 菜单添加
    Route::post('menu',AUTH_BASE_PATH.'\Permissions@add');
    // 菜单修改
    Route::put('menu/:id',AUTH_BASE_PATH.'\Permissions@edit');
    // 菜单删除
    Route::delete('menu/:id',AUTH_BASE_PATH.'\Permissions@delete');
    // 菜单设置状态
    Route::put('menu-status/:id',AUTH_BASE_PATH.'\Permissions@status');
    // 菜单tree
    Route::get('permissionsTree',AUTH_BASE_PATH.'\Permissions@tree');

    // 管理员
    Route::get('admin',AUTH_BASE_PATH.'\Admin@list');
    // 管理员详情
    Route::get('admin-info/:id',AUTH_BASE_PATH.'\Admin@info');
    // 管理员添加
    Route::post('admin',AUTH_BASE_PATH.'\Admin@add');
    // 管理员编辑
    Route::put('admin/:id',AUTH_BASE_PATH.'\Admin@edit');
    // 管理员删除
    Route::delete('admin/:ids',AUTH_BASE_PATH.'\Admin@delete');
    // 更改管理员密码
    Route::put('admin-password/:id',AUTH_BASE_PATH.'\Admin@editPassword');

    // 角色列表
    Route::get('role',AUTH_BASE_PATH.'\Role@list');
    // 角色详情
    Route::get('role-info/:id',AUTH_BASE_PATH.'\Role@info');
    // 角色添加
    Route::post('role',AUTH_BASE_PATH.'\Role@add');
    // 角色编辑
    Route::put('role/:id',AUTH_BASE_PATH.'\Role@edit');
    // 角色删除
    Route::delete('role/:ids',AUTH_BASE_PATH.'\Role@delete');
    // 角色状态
    Route::delete('role-status/:id',AUTH_BASE_PATH.'\Role@status');
    // 角色列表tree
    Route::get('role-tree',AUTH_BASE_PATH.'\Role@tree');
});