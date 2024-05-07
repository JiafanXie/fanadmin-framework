<?php

namespace core\src\http\service\admin\addon\cloud;

use app\service\admin\niucloud\NiucloudService;
use core\lib\cloud\BaseCloudClient;
use core\util\niucloud\http\Response;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * 云消息服务
 * Class NotifyCloudService
 * @package core\lib\addon\cloud
 */
class NotifyCloudService extends BaseCloudClient
{
    /**
     * 官网消息推送
     * @return void
     */
    public function notify(){
        //校验证书
        $this->validateSignature();
        $message = request()->param('Message');
        $message_type = request()->param('MsgType');
        switch($message_type){
            case 'auth':
                $this->setAccessToken($message['AccessToken']['token']);
                break;
        }
        return success();
    }
}