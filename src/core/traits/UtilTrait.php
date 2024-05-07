<?php

namespace core\traits;


use app\admin\model\PageModel;
use core\provider\Auth;
use think\Request;
use core\provider\Uploader;
use core\model\user\UserModel;
use core\exception\FanException;

/**
 * 公共接口 trait
 * Trait Util
 * @package core\controller\traits
 */
trait UtilTrait {

    /**
     * 上传图片（不存 file 表）
     * @param Request $request
     * @return \think\response\Json
     */
    public function upload (Request $request) {
        $file = $request->file('file');
        // $group = $request->param('group', 'default');
        $result = Uploader::uploadSim($file, 'ugc');        // 前端固定上传目录
        return success('上传成功', $result);
    }

    /**
     * 客服通用 token,通过用户信息换取
     * @return \think\response\Json
     */
    public function unifiedToken (Request $request) {
        $root = substr(request()->root(), 1);
        if ($root == 'shop') {
            // 商城通过 token 自动获取
            $user = Auth::guard('user');
        } else {
            // 客服没有登录模块，所以直接传 id
            $id = $request->param('id');
            $user = UserModel::find($id);
        }
        if (!$user) {
            throw new FanException('用户不存在');
        }
        $token = $user->getUnifiedToken('user:' . $user->id);
        return success('获取成功', [
            'token' => $token
        ]); 
    }

    /**
     * 获取页面数据
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function page (Request $request) {
        $id = $request->param('id',0);
        $item = PageModel::where('id',$id)->where('status','normal')->field('page')->find();
        if($item){
            $page = $item['page']; 
         }else{
             $page = [];
         }
        return success(compact('page'));
    }
}
