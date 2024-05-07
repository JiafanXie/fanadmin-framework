<{route}>

Route::group('<{routeGroup}>', function () {

    // <{notes}>列表
    Route::get('<{routeName}>', '<{routePath}>lists');
    // <{notes}>详情
    Route::get('<{routeName}>/:id', '<{routePath}>info');
    // <{notes}>添加
    Route::post('<{routeName}>', '<{routePath}>add');
    // <{notes}>编辑
    Route::put('<{routeName}>/:id', '<{routePath}>edit');
    // <{notes}>删除
    Route::delete('<{routeName}>/:id', '<{routePath}>delete');
    <{withRoute}>
})->middleware([
    AdminTokenCheck::class,
    AdminAuthCheck::class
]);
