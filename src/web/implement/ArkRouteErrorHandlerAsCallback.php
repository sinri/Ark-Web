<?php


namespace sinri\ark\web\implement;


use sinri\ark\web\ArkRouteErrorHandlerInterface;

class ArkRouteErrorHandlerAsCallback implements ArkRouteErrorHandlerInterface
{

    /**
     * Override this if a special render is needed
     * @param array $errorMessage
     * @param int $httpCode
     */
    public function requestErrorCallback($errorMessage, $httpCode)
    {
        echo '<h1>Error</h1>' . PHP_EOL;
        echo "<p>HTTP Status Code : {$httpCode}</p>" . PHP_EOL;
        foreach ($errorMessage as $key => $value) {
            echo "<p>{$key} : {$value}</p>" . PHP_EOL;
        }
    }

    public function execute($errorData = [], $http_code = 404)
    {
        $this->requestErrorCallback($errorData, $http_code);
    }
}