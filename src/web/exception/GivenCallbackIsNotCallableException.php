<?php


namespace sinri\ark\web\exception;


use Exception;
use Throwable;

/**
 * Class GivenCallbackIsNotCallableException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 */
class GivenCallbackIsNotCallableException extends Exception
{
    public function __construct($callable = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("The given callback is not callable: " . json_encode($callable), $code, $previous);
    }
}