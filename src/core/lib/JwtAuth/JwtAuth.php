<?php

namespace FanAdmin\lib\JwtAuth;


use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use \think\Exception;

/**
 * jwt类
 * Class JwtAuth
 * @package FanAdmin\lib\JwtAuth
 */
class JwtAuth {

    /**
     * 生成验签
     * @param $uid
     * @return string
     */
    static public function signToken ($uid) {
        // 这里是自定义的一个随机字串，应该写在config文件中的，解密时也会用，相当    于加密中常用的 盐  salt
        $key   = '!@#$%*&';
        $token = array(
            // 签发者 可以为空
            "iss"  => $key,
            // 面象的用户，可以为空
            "aud"  => '',
            // 签发时间
            "iat"  => time(),
            // 在什么时候jwt开始生效  （这里表示生成100秒后才生效）
            "nbf"  => time() + 3,
            // token 过期时间 （一周）
            "exp"  => time() + 60*60*24*7,
            // 记录的userid的信息，这里是自已添加上去的，如果有其它信息，可以再添加数组的键值对
            "data" => [
                'uid' => $uid,
            ]
        );
        //根据参数生成了 token
        $jwt = JWT::encode($token, $key, "HS256");
        return $jwt;
    }

    /**
     * 验证token
     * @param $token
     * @return array|int[]
     */
    static public function checkToken ($token) {
        $key    = '!@#$%*&';
        $status = array("code" => 2);
        try {
            // 当前时间减去60，把时间留点余地
            JWT::$leeway = 60;
            // HS256方式，这里要和签发的时候对应
            $decoded = JWT::decode($token, new Key($key,'HS256'));
            $arr     = (array)$decoded;
            $res['code'] = 1;
            $res['data'] = $arr['data']->uid;
            return $res;
        } catch (\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
            $status['msg'] = "签名不正确";
            return $status;
        } catch (\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
            $status['msg'] = "token失效";
            return $status;
        } catch (\Firebase\JWT\ExpiredException $e) { // token过期
            $status['msg'] = "token失效";
            return $status;
        } catch (Exception $e) { //其他错误
            $status['msg'] = "未知错误";
            return $status;
        }
    }
}
