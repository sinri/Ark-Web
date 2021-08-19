<?php


namespace sinri\ark\web;


use Exception;

interface ArkRouteErrorHandlerInterface
{
    /**
     * @param Exception $error
     * @param int $http_code
     * @return void
     * Do not throw Exception from inside!
     * @since 3.4.8 $errorData renamed to $error and its type might be Exception
     * @since 3.5.0 changed definition
     */
    public function execute(Exception $error, int $http_code);

}