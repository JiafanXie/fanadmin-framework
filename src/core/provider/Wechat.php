<?php

namespace core\provider;


use think\Facade;

/**
 * wechat工具类
 * Class Wechat
 * @package core\facade
 */
class Wechat extends Facade {

    /**
     * @return \EasyWeChat\OfficialAccount\Application
     */
    public static function officialAccount ($app = '') {
        if ($app !== '') {
            $app .= '.';
        }
        return app($app . 'wechat.official_account');
    }

    /**
     * @return \EasyWeChat\MiniProgram\Application
     */
    public static function miniProgram ($app = '') {
        if ($app !== '') {
            $app .= '.';
        }
        return app('wechat.mini_program');
    }

    /**
     * @return \EasyWeChat\OpenPlatform\Application
     */
    public static function openPlatform ($app = '') {
        if ($app !== '') {
            $app .= '.';
        }
        return app('wechat.open_platform');
    }

    /**
     * @return \EasyWeChat\Work\Application
     */
    public static function work ($app = '') {
        if ($app !== '') {
            $app .= '.';
        }
        return app('wechat.work');
    }
}
