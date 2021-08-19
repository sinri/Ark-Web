<?php


namespace sinri\ark\web\exception;


use RuntimeException;

/**
 * Class WebRequestMethodUnacceptableException
 * @package sinri\ark\web\exception
 * @since 3.4.4
 * @since 3.5.0 make code default as 405
 */
class ArkWebRequestMethodUnacceptableException extends RuntimeException
{
    public function __construct($message = "", $code = 405)
    {
        parent::__construct($message, $code);
    }
}