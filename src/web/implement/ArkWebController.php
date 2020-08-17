<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 21:37
 */

namespace sinri\ark\web\implement;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\ArkWebService;

class ArkWebController
{
    /**
     * @var array|null
     * It might be renamed in the future, but now it would not be changed for stability
     */
    protected $filterGeneratedData;

    /**
     * ArkWebController constructor.
     * You should process filters-generated-data here if needed,
     * the property $filterGeneratedData is set here.
     */
    public function __construct()
    {
        $this->filterGeneratedData = ArkWebService::getSharedInstance()->getSharedData();//Ark()->webService()->getSharedData();
    }

    /**
     * @param string|array $name
     * @param mixed $default
     * @param null|string $regex
     * @return mixed
     * @since 3.1.7
     */
    protected function _readFilterGeneratedData($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($this->filterGeneratedData, $name, $default, $regex);
    }

    /**
     * @return ArkWebInput
     * @since 1.1 method added
     */
    protected function _getInputHandler()
    {
        return ArkWebInput::getSharedInstance();//Ark()->webInput();
    }

    /**
     * @return ArkWebOutput
     * @since 1.1 method added
     */
    protected function _getOutputHandler()
    {
        return ArkWebOutput::getSharedInstance();//Ark()->webOutput();
    }

    /**
     * @param string|array $name
     * @param mixed $default
     * @param null|string $regex
     * @param null|Exception $error
     * @return mixed
     * @since 1.1 method added
     */
    protected function _readRequest($name, $default = null, $regex = null, &$error = null)
    {
        return //Ark()->webInput()
            ArkWebInput::getSharedInstance()
            ->readRequest($name, $default, $regex, $error);
    }

    /**
     * @param string|array $name
     * @param mixed $default
     * @param null|string $regex
     * @return mixed
     */
    protected function _readCookie($name, $default = null, $regex = null)
    {
        return //Ark()->webInput()
            ArkWebInput::getSharedInstance()
            ->readCookie($name, $default, $regex);
    }

    /**
     * @param string|array $name
     * @param mixed $default
     * @param null|string $regex
     * @return mixed
     * @since 3.1.7
     */
    protected function _readSession($name, $default = null, $regex = null)
    {
        return //Ark()->webInput()
            ArkWebInput::getSharedInstance()
            ->readSession($name, $default, $regex);
    }

    /**
     * @param string|array $name
     * @param mixed $default
     * @param null|string $regex
     * @return mixed
     * @since 3.1.7
     */
    protected function _readServer($name, $default = null, $regex = null)
    {
        return //Ark()->webInput()
            ArkWebInput::getSharedInstance()
            ->readServer($name, $default, $regex);
    }

    /**
     * @param string $name
     * @param callable|string|null $checker An anonymous function `f(value,name)` or a regular expression, else would not check any more
     * @return mixed
     * @throws Exception
     * @since 2.6
     */
    protected function _readIndispensableRequest($name, $checker)
    {
        return //Ark()->webInput()
            ArkWebInput::getSharedInstance()
            ->readIndispensableRequest($name, $checker);
    }

    /**
     * If you want to track a request, use this method to get the serial number of a request.
     * @return string
     */
    protected function _getRequestSerial()
    {
        return //Ark()->webService()
            ArkWebService::getSharedInstance()
            ->getRequestSerial();
    }

    /**
     * @param string[] $expectedMethods
     * @throws Exception
     * @since 3.1.7
     */
    protected function _assertMetExpectedMethods($expectedMethods)
    {
        if (!in_array($this->_getInputHandler()->getRequestMethod(), $expectedMethods)) {
            throw new Exception("Access with unexpected method", 405);
        }
    }

    /**
     * @param mixed $json anything in json to be packaged to be responded
     * @param int $httpCode
     * @since 3.1.7
     */
    protected function _sayJson($json, $httpCode = 200)
    {
        //Ark()->webOutput()
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->json($json);
    }

    /**
     * @param mixed $data
     * @param int $httpCode
     */
    protected function _sayOK($data = "", $httpCode = 200)
    {
        //Ark()->webOutput()
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_OK, $data);
    }

    /**
     * @param mixed $error
     * @param int $httpCode
     */
    protected function _sayFail($error = "", $httpCode = 200)
    {
        //Ark()->webOutput()
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->setContentTypeHeader(ArkWebOutput::CONTENT_TYPE_JSON)
            ->jsonForAjax(ArkWebOutput::AJAX_JSON_CODE_FAIL, $error);
    }

    /**
     * @param string $templateFile
     * @param array $params
     * @param int $httpCode
     */
    protected function _showPage($templateFile, $params = [], $httpCode = 200)
    {
        //try {
        //Ark()->webOutput()
        ArkWebOutput::getSharedInstance()
            ->sendHTTPCode($httpCode)
            ->displayPage($templateFile, $params);
//        } catch (Exception $e) {
//            echo $e->getMessage();
//        }
    }
}