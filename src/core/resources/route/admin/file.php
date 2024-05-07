<?php

use think\facade\Route;


CONST OSS_BASE_PATH = "FanAdmin\src\http\controller\admin\oss";
/**
 * 文件相关路由
 */
Route::group('file', function () {
    // 图片上传
    Route::post('image', OSS_BASE_PATH.'\Upload@image');
    // 视频上传
    Route::post('video', OSS_BASE_PATH.'\Upload@video');
    // 证书上传
    Route::post('cert', OSS_BASE_PATH.'\Upload@video');
    // 其它文件上传
    Route::post('file', OSS_BASE_PATH.'\Upload@file');
    // 文件列表
    Route::get('list/:type', OSS_BASE_PATH.'\Upload@lists');
    // 文件分组列表
    Route::get('group/:type', OSS_BASE_PATH.'\Upload@list');
    // 文件分组添加
    Route::post('group/:type', OSS_BASE_PATH.'\Upload@add');
    // 文件分组删除
    Route::delete('group/:id/:type', OSS_BASE_PATH.'\Upload@delete');
    // 文件删除
    Route::delete('delete/:ids', OSS_BASE_PATH.'\Upload@batchDel');
});