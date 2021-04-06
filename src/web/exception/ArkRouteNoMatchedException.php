<?php


namespace sinri\ark\web\exception;


use Exception;
use Throwable;

/**
 * Class ArkRouteNoMatchedException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 */
class ArkRouteNoMatchedException extends Exception
{
    public function __construct($method = "", $incomingPath = "", $code = 404, Throwable $previous = null)
    {
        parent::__construct("No route matched for path [{$incomingPath}] with method [{$method}]", $code, $previous);
    }
}