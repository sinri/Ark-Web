<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\web\ArkRouteErrorHandlerInterface;

class ArkRouteErrorHandlerAsCallback implements ArkRouteErrorHandlerInterface
{

    /**
     * @param Exception $error
     * @param int $http_code
     */
    public function execute($error, $http_code = 404)
    {
        echo '<h1>Error</h1>' . PHP_EOL;
        echo "<p>HTTP Status Code : {$http_code}</p>" . PHP_EOL;

        echo '<p>Exception Code: ' . $error->getCode() . '</p>' . PHP_EOL;
        echo '<p>Exception Message: ' . $error->getMessage() . '</p>' . PHP_EOL;
        echo '<p>Exception Trace: ' . $error->getTraceAsString() . '</p>' . PHP_EOL;
    }
}