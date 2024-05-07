<?php

namespace core\exception;


use RuntimeException;
use Throwable;

/**
 * admin错误异常处理类
 * Class AdminException
 * @package core\exception
 */
class AdminException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
