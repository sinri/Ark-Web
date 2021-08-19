<?php


namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\core\exception\ArkNestedException;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkRouteErrorHandlerInterface;
use sinri\ark\web\exception\ArkWebRequestFailed;

class ArkRouteErrorHandlerAsJson implements ArkRouteErrorHandlerInterface
{
    /**
     * @param Exception $error
     * @param int $http_code
     * Do not throw Exception from inside!
     */
    public function execute(Exception $error, int $http_code)
    {
        $data = [
            'error' => $error->getMessage()
        ];
        if (is_a($error, ArkWebRequestFailed::class)) {
            $data['detail'] = $error->getDetail();
        }
        if (is_a($error, ArkNestedException::class)) {
            $data['nested'] = $error->getNestedMessage();
        }
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($http_code)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $data);
    }
}