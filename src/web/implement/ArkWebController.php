<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 21:37
 */

namespace sinri\ark\web\implement;


use sinri\ark\core\ArkHelper;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\io\exception\RequestedFieldMissingError;
use sinri\ark\io\exception\RequestFieldInvalidatedError;
use sinri\ark\web\ArkWebService;
use sinri\ark\web\exception\ArkWebRequestMethodUnacceptableException;

class ArkWebController
{
//    /**
//     * @var array|null
//     * @deprecated since 3.5.x Do not use it directly, if needed, use `_readFilterGeneratedData` instead
//     * It might be renamed in the future, but now it would not be changed for stability
//     */
//    protected ?array $filterGeneratedData;

    /**
     * ArkWebController constructor.
     * You should process filters-generated-data here if needed,
     * the property $filterGeneratedData is set here.
     */
    public function __construct()
    {
//        $this->filterGeneratedData = ArkWebService::getSharedInstance()->getSharedData();
    }

    /** @since 3.1.7 */
    protected function _readFilterGeneratedData(array|string $name, mixed $default = null, ?string $regex = null)
    {
        return ArkHelper::readTarget(ArkWebService::getSharedInstance()->getSharedData(), $name, $default, $regex);
    }

    /**
     * @return ArkWebInput
     * @since 1.1 method added
     */
    protected function _getInputHandler(): ArkWebInput
    {
        return ArkWebInput::getSharedInstance();
    }

    /**
     * @return ArkWebOutput
     * @since 1.1 method added
     */
    protected function _getOutputHandler(): ArkWebOutput
    {
        return ArkWebOutput::getSharedInstance();
    }

    /** @since 3.1.7 */
    protected function _readServer(array|string $name, mixed $default = null, ?string $regex = null)
    {
        return ArkWebInput::getSharedInstance()
            ->readServer($name, $default, $regex);
    }

    /**
     * @param callable|string|null $checker An anonymous function `f(value,name)` or a regular expression, else would not check any more
     * @throws RequestFieldInvalidatedError
     * @throws RequestedFieldMissingError
     * @since 2.6
     * @since 3.4.6 parameter `checker` is default as `null`
     */
    protected function _readIndispensableRequest(string $name, callable|string|null $checker = null)
    {
        return ArkWebInput::getSharedInstance()
            ->readIndispensableRequest($name, $checker);
    }

    /**
     * If you want to track a request, use this method to get the serial number of a request.
     * @return string
     */
    protected function _getRequestSerial(): string
    {
        return ArkWebService::getSharedInstance()
            ->getRequestSerial();
    }

    /**
     * @param string[] $expectedMethods
     * @throws ArkWebRequestMethodUnacceptableException
     * @since 3.4.4 throw specified exception
     * @since 3.1.7
     */
    protected function _assertMetExpectedMethods(array $expectedMethods): void
    {
        if (!in_array($this->_getInputHandler()->getRequestMethod(), $expectedMethods)) {
            throw new ArkWebRequestMethodUnacceptableException("Access with unexpected method", 405);
        }
    }

    /**
     * @param mixed $json anything in json to be packaged to be responded
     * @param int $httpCode
     * @since 3.1.7
     */
    protected function _sayJson(mixed $json, int $httpCode = 200): void
    {
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->json($json);
    }

    /**
     * @param mixed|string $data
     * @param int $httpCode
     * @param mixed|null $debugInfo @since 3.4.7
     */
    protected function _sayOK(mixed $data = "", int $httpCode = 200, mixed $debugInfo = null): void
    {
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_OK, $data, 0, 512, $debugInfo);
    }

    /**
     * @param mixed|string $error
     * @param int $httpCode
     * @param mixed|null $debugInfo @since 3.4.7
     * @deprecated since 3.5.0 It is now recommended to use `throw new ArkWebRequestFailed` instead
     */
    protected function _sayFail(mixed $error = "", int $httpCode = 200, mixed $debugInfo = null): void
    {
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $error, 0, 512, $debugInfo);
    }

    /**
     * @param string $templateFile
     * @param array $params
     * @param int $httpCode
     */
    protected function _showPage(string $templateFile, array $params = [], int $httpCode = 200): void
    {
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->displayPage($templateFile, $params);
    }
}