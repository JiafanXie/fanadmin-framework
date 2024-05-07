<?php

namespace core\lib\oauth;


/**
 * 微信小程序授权
 * Class Weapp
 * @package core\lib\oauth
 */
class Weapp extends BaseOauth {


    protected function initialize (array $config = []) {
        parent::initialize ($config);
    }

    public function oauth (string $code = null, array $options = []) {

    }
}