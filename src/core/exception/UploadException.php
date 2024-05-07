<?php

namespace core\exception;


use RuntimeException;
use Throwable;

/**
 * 上传错误异常处理类
 * Class UploadException
 * @package core\exception
 */
class UploadException extends RuntimeException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
