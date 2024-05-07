<?php

namespace core\exception;


use RuntimeException;
use Throwable;

/**
 * 公共错误异常处理类
 * Class CommonException
 * @package core\exception
 */
class CommonException extends RuntimeException{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
