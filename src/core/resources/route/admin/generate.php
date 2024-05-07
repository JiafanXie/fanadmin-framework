<?php

use think\facade\Route;


CONST GENERATE_BASE_PATH = "FanAdmin\src\http\controller\admin\generate";
/**
 * 一键生成相关路由
 */
Route::group('curd', function () {
    // 生成列表
    Route::get('generate', GENERATE_BASE_PATH.'\Generate@list');
    // 生成详情
    Route::get('generate/:id', GENERATE_BASE_PATH.'\Generate@info');
    // 添加代码
    Route::post('generate', GENERATE_BASE_PATH.'\Generate@add');
    // 编辑生成
    Route::put('generate/:id', GENERATE_BASE_PATH.'\Generate@edit');
    // 删除
    Route::delete('generate/:id', GENERATE_BASE_PATH.'\Generate@delete');
    // 生成代码
    Route::post('create', GENERATE_BASE_PATH.'\Generate@create');
    // 表
    Route::get('table', GENERATE_BASE_PATH.'\Generate@tableList');
    // 预览
    Route::get('view/:id', GENERATE_BASE_PATH.'\Generate@view');
    // 检查
    Route::get('check-file/:id', GENERATE_BASE_PATH.'\Generate@checkFile');
    // 关联
    Route::get('table-column/:table_name', GENERATE_BASE_PATH.'\generate\Generate@getTableColumn');
    // 全部模型
    Route::get('all-model', GENERATE_BASE_PATH.'\Generate@getModels');
    // 获取字段信息
    Route::get('model-table-column', GENERATE_BASE_PATH.'\Generate@getModelTableColumn');
});