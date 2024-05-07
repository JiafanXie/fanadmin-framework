<?php

namespace FanAdmin\exception;


use RuntimeException;
use Throwable;

/**
 * 权限错误异常处理类
 * Class AuthException
 * @package FanAdmin\exception
 */
class AuthException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
