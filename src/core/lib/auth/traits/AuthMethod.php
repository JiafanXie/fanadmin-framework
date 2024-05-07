<?php

namespace core\lib\auth\traits;


use core\exception\FanException;

/**
 *
 * Trait AuthMethod
 * @package core\lib\auth\traits
 */
trait AuthMethod {

    /**
     * @var string
     */
    protected $rememberTokenName = 'remember_token';

    /**
     * 获取用户主键
     * @return mixed
     */
    public function getAuthIdentifierName () {
        return $this->getPk();
    }

    /**
     * 获取主键值
     * @return mixed
     */
    public function getAuthIdentifier () {
        return $this->{$this->getAuthIdentifierName()};
    }

    /**
     * 获取加密密码
     * @return mixed
     */
    public function getAuthPassword () {
        return $this->password;
    }

    /**
     * 登录账号需要查询的字段
     * @return string[]
     */
    public function accountname () {
        return ['account', 'mobile', 'email'];
    }

    /**
     * 获取加密密码
     * @param $password
     * @param $salt
     * @return string
     */
    public function encryptPassword ($password, $salt) {
        return md5(md5($password) . $salt);
    }

    /**
     * 获取 记住我 token
     * @return string
     */
    public function getRememberToken () {
        if (!empty($this->getRememberTokenName())) return (string) $this->{$this->getRememberTokenName()};
    }

    /**
     * 设置记住我token
     * @param $value
     */
    public function setRememberToken ($value) {
        if (!empty($this->getRememberTokenName())) $this->{$this->getRememberTokenName()} = $value;
    }

    /**
     * 记住我字段
     * @return string
     */
    public function getRememberTokenName () {
        return $this->rememberTokenName;
    }
}
