<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkRouteErrorHandlerInterface;

class ArkRouteErrorHandlerAsJson implements ArkRouteErrorHandlerInterface
{
    /**
     * @param array|Exception $error
     * @param int $http_code
     * Do not throw Exception from inside!
     */
    public function execute($error, $http_code = 404)
    {
        $data = [];
        if (is_array($error)) {
            $data = $error;
        } elseif (is_a($error, Exception::class)) {
            $data = ['exception_code' => $error->getCode(), 'exception_message' => $error->getMessage()];
        }
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($http_code)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $data);
    }
}