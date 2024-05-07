<?php

namespace core\exception;


use RuntimeException;
use Throwable;

/**
 * 通知错误异常处理类
 * Class NoticeException
 * @package core\exception
 */
class NoticeException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
