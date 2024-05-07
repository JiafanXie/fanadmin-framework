<?php

namespace core\exception;


use RuntimeException;
use Throwable;

/**
 * 服务异常处理类
 * Class ServerException
 * @package core\exception
 */
class ServerException extends RuntimeException
{
    public function __construct($message = "", $code = 409, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
