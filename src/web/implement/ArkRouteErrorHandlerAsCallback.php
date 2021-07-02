<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\web\ArkRouteErrorHandlerInterface;

class ArkRouteErrorHandlerAsCallback implements ArkRouteErrorHandlerInterface
{

    /**
     * Override this if a special render is needed
     * @param array|Exception $error
     * @param int $httpCode
     */
    public function requestErrorCallback($error, $httpCode)
    {
        echo '<h1>Error</h1>' . PHP_EOL;
        echo "<p>HTTP Status Code : {$httpCode}</p>" . PHP_EOL;
        if (is_array($error)) {
            foreach ($error as $key => $value) {
                echo "<p>{$key} : {$value}</p>" . PHP_EOL;
            }
        } elseif (is_a($error, Exception::class)) {
            echo '<p>Exception Code: ' . $error->getCode() . '</p>' . PHP_EOL;
            echo '<p>Exception Message: ' . $error->getMessage() . '</p>' . PHP_EOL;
            echo '<p>Exception Trace: ' . $error->getTraceAsString() . '</p>' . PHP_EOL;
        } else {
            echo '<pre>' . print_r($error, true) . '</pre>' . PHP_EOL;
        }
    }

    /**
     * @param Exception $error
     * @param int $http_code
     */
    public function execute($error, $http_code = 404)
    {
        $this->requestErrorCallback($error, $http_code);
    }
}