<?php


namespace sinri\ark\web\exception;


use Exception;

/**
 * Class ArkRouteNoMatchedException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 * @since 3.5.0 make code default as 404
 */
class ArkRouteNoMatchedException extends Exception
{
    public function __construct($method = "", $incomingPath = "")
    {
        parent::__construct("No route matched for path [$incomingPath] with method [$method]", 404);
    }
}