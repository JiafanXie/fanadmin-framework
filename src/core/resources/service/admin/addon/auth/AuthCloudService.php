<?php

namespace core\src\http\service\admin\addon\auth;

use core\lib\cloud\CloudService;
use core\lib\cloud\BaseCloudClient;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * 云授权服务
 * Class AuthCloudService
 * @package core\lib\addon\cloud
 */
class AuthCloudService extends BaseCloudClient
{

    /**
     * @return array|Response|object|ResponseInterface
     * @throws GuzzleException
     */
    public function getAuthInfo()
    {
        $auth_info = $this->httpGet('authinfo', ['code' => $this->code, 'secret' => $this->secret, 'product_key' => self::PRODUCT ]);
        if(!empty($auth_info['data'])){
            $auth_info['data']['address_type'] = true;
            if($auth_info['data']['site_address'] != $_SERVER['HTTP_HOST']) $auth_info['data']['address_type'] = false;
        }
        return $auth_info;
    }


}
