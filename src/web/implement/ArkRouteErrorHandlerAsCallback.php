<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\web\ArkRouteErrorHandlerInterface;
use sinri\ark\web\exception\ArkWebRequestFailed;

class ArkRouteErrorHandlerAsCallback implements ArkRouteErrorHandlerInterface
{

    /**
     * @param Exception $error
     * @param int $http_code
     */
    public function execute(Exception $error, int $http_code)
    {
        echo '<h1>Error</h1>' . PHP_EOL;
        echo "<p>HTTP Status Code : $http_code</p>" . PHP_EOL;

        echo '<p>Exception Code: ' . $error->getCode() . '</p>' . PHP_EOL;
        echo '<p>Exception Message: ' . $error->getMessage() . '</p>' . PHP_EOL;
        if (is_a($error, ArkWebRequestFailed::class)) {
            echo '<p>Exception Detail: <br>' . PHP_EOL;
            $detail = $error->getDetail();
            foreach ($detail as $k => $v) {
                echo "&nbsp;&nbsp;[$k]&nbsp;&nbsp;$v<br>" . PHP_EOL;
            }
            echo '</p>' . PHP_EOL;
        }
        echo '<p>Exception Trace: ' . $error->getTraceAsString() . '</p>' . PHP_EOL;
    }
}