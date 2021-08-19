<?php


namespace sinri\ark\web\exception;


use Exception;

/**
 * Class GivenCallbackIsNotCallableException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 * @since 3.5.0 make code default as 404
 */
class GivenCallbackIsNotCallableException extends Exception
{
    public function __construct($callable)
    {
        parent::__construct("The given callback is not callable: " . json_encode($callable), 404);
    }
}