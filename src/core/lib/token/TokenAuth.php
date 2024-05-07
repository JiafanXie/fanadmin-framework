<?php

namespace core\lib\token;


use Firebase\JWT\JWT;
use think\facade\Cache;
use think\facade\Env;
use think\Response;

/**
 * token工具类
 * Class TokenAuth
 * @package core\lib
 */
class TokenAuth {

    /**
     * 创建token
     * @param int $id
     * @param string $type
     * @param array $params
     * @param int $expire_time
     * @return array
     */
    public static function createToken (int $id, string $type, array $params = [], int $expire_time = 0): array {
        $host = app()->request->host();
        $time = time();
        $params += [
            'iss' => $host,
            'aud' => $host,
            'iat' => $time,
            'nbf' => $time,
            'exp' => $time + $expire_time,
        ];
        $params['jti'] = $id . "_" . $type;
        $token = JWT::encode($params, Env::get('app.app_key', 'niucloud456$%^'), 'HS256');
        $cache_token = Cache::get("token_" . $params['jti']);
        $cache_token_arr = $cache_token ?: [];
        $cache_token_arr[] = $token;
        Cache::tag("token")->set("token_" . $params['jti'], $cache_token_arr);
        return compact('token', 'params');
    }

    /**
     * 解析token
     * @param string $token
     * @param string $type
     * @return array
     * @throws \JsonException
     */
    public static function parseToken (string $token, string $type): array {
        $payload = JWT::decode($token, Env::get('app.app_key', 'cloud456$%^'), ['HS256']);
        if (!empty($payload)) {
            $token_info = json_decode(json_encode($payload), true, 512, JSON_THROW_ON_ERROR);
            if (explode("_", $token_info['jti'])[1] != $type) {
                return [];
            }
            if (!empty($token_info) && !in_array($token, Cache::get('token_' . $token_info['jti'], []))) {
                return [];
            }
            return $token_info;
        } else {
            return [];
        }
    }

    /**
     * 清理token
     * @param int $id
     * @param string $type
     * @param string|null $token
     * @return \think\response\Json
     */
    public static function clearToken (int $id, string $type, ?string $token = '') {
        if (!empty($token)) {
            $token_cache = Cache::get("token_" . $id . "_" . $type, []);
            //todo 也可以通过修改过期时间来实现
            if (!empty($token_cache)) {
                if (($key = array_search($token, $token_cache)) !== false) {
                    array_splice($token_cache, $key, 1);
                }
                Cache::set("token_" . $id . "_" . $type, $token_cache);
            }
        } else {
            Cache::set("token_" . $id . "_" . $type, []);
        }
        return success();
    }
}
