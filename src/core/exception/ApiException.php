<?php

namespace core\exception;


use RuntimeException;
use Throwable;

/**
 * api错误异常处理类
 * Class ApiException
 * @package core\exception
 */
class ApiException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
