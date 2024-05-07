<?php

namespace core\exception;


use think\Exception;

/**
 * 误异常处理类
 */
class FanException extends Exception
{

    const LOGIN_ERROR  = 401;
    const ACCESS_ERROR = 403;

    /**
     * 自定义错误码
     */
    public $error_code = 1;

    /**
     * http 状态码
     */
    public $status_code = 200;

    /**
     * 错误信息
     */
    public $message    = '';

    /**
     * 抛出错误信息
     * @param string $msg
     * @param int $error_code
     * @param int $status_code
     * @return mixed
     */
    public function returntnMessage($msg = '', $error_code = 1, $status_code = 200)
    {
        $this->message     = $msg;
        $this->error_code  = $error_code;
        $this->status_code = $status_code;
        return $this;
    }

    /**
     * 抛出登录错误
     * @return $this
     */
    public function loginError()
    {
        return $this->returntnMessage("您还没有登录，请先登录", 1, self::LOGIN_ERROR);
    }

    /**
     * 抛出无权错误信息
     * @return $this
     */
    public function accessError()
    {
        return $this->returntnMessage("无操作权限", 1, self::ACCESS_ERROR);
    }
}