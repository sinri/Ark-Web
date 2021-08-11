<?php


namespace sinri\ark\io\traits;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\exception\LookUpTargetException;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\exception\RequestedFieldMissingError;
use sinri\ark\io\exception\RequestFieldInvalidatedError;
use sinri\ark\io\WebInputFileUploadHelper;
use sinri\ark\io\WebInputHeaderHelper;
use sinri\ark\io\WebInputIPHelper;

/**
 * Trait ArkWebInputTrait
 * @package sinri\ark\io\traits
 * @since 3.4.2
 */
trait ArkWebInputTrait
{
    protected $headerHelper;
    protected $ipHelper;
    protected $uploadFileHelper;
    protected $rawPostBody;
    protected $rawPostBodyParsedAsJson;

    public function __construct()
    {
        $this->headerHelper = new WebInputHeaderHelper();
        $this->ipHelper = new WebInputIPHelper();
        $this->uploadFileHelper = new WebInputFileUploadHelper();
        $this->rawPostBody = file_get_contents('php://input');
        $this->rawPostBodyParsedAsJson = @json_decode($this->rawPostBody, true);
    }

    /**
     * @return WebInputFileUploadHelper
     */
    public function getUploadFileHelper(): WebInputFileUploadHelper
    {
        return $this->uploadFileHelper;
    }

    /**
     * @return WebInputHeaderHelper
     */
    public function getHeaderHelper(): WebInputHeaderHelper
    {
        return $this->headerHelper;
    }

    /**
     * @return WebInputIPHelper
     */
    public function getIpHelper(): WebInputIPHelper
    {
        return $this->ipHelper;
    }

    /**
     * @return bool|string
     */
    public function getRawPostBody()
    {
        return $this->rawPostBody;
    }

    /**
     * @return mixed
     */
    public function getRawPostBodyParsedAsJson()
    {
        return $this->rawPostBodyParsedAsJson;
    }

    /**
     * When value is null:
     *  throw RequestedFieldMissingException
     * When checker is:
     *  Callable: throw RequestFieldInvalidatedError when the checker (`f(value,name)`) return false with value;
     *  Regex String (such as `/^\d+$/`): throw RequestFieldInvalidatedError when the value does not match it;
     *  Null: throw RequestFieldInvalidatedError when the value is an empty string;
     *  Else: not check anything.
     *
     * @param string $name
     * @param callable|string|null $checker An anonymous function `f(value,name)` or a valid regular expression, else would not check any more
     * @return mixed
     * @throws RequestFieldInvalidatedError
     * @throws RequestedFieldMissingError
     * @since 2.6
     * @since 2.8.1 add a secondary parameter to checker when it is a callable function
     */
    public function readIndispensableRequest(string $name, $checker = null)
    {
        $value = $this->readRequest($name);
        if ($value === null) {
            throw new RequestedFieldMissingError($name);
        }
        if (is_callable($checker)) {
            if (!call_user_func_array($checker, [$value, $name])) {
                throw new RequestFieldInvalidatedError($name, $value);
            }
        } elseif (is_string($checker) && strlen($checker) > 2) {
            // at least it would be have `/` in head and tail
            if (!preg_match($checker, $value)) {
                throw new RequestFieldInvalidatedError($name, $value);
            }
        } elseif ($checker === null) {
            if ($value === '') {
                throw new RequestFieldInvalidatedError($name, $value);
            }
        }
        return $value;
    }

    /**
     * @param string|array $name
     * @param null|mixed $default
     * @param null|string $regex
     * @param null|LookUpTargetException $error
     * @return mixed
     */
    public function readRequest($name, $default = null, $regex = null, &$error = null)
    {
        $value = ArkHelper::readTarget($_REQUEST, $name, $default, $regex, $error);
        $content_type = $this->headerHelper->getHeader("CONTENT-TYPE", null, '/^application\/json/');
        if (
            $content_type !== null
            //preg_match('/^application\/json(;.+)?$/', $content_type)
        ) {
            if (is_array($this->rawPostBodyParsedAsJson)) {
                $value = ArkHelper::readTarget($this->rawPostBodyParsedAsJson, $name, $default, $regex, $error);
            }
        }

        return $value;
    }

    public function readHeader($name, $default = null, $regex = null)
    {
        return $this->headerHelper->getHeader($name, $default, $regex);
    }

    public function readSession($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SESSION, $name, $default, $regex);
    }

    public function readCookie($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_COOKIE, $name, $default, $regex);
    }

    public function readGet($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_GET, $name, $default, $regex);
    }

    public function readPost($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_POST, $name, $default, $regex);
    }

    /**
     * @param $name
     * @param null $default
     * @param null $regex
     * @return mixed|null
     * @since 2.3
     */
    public function readPostedJson($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($this->rawPostBodyParsedAsJson, $name, $default, $regex, $error);
    }

    /**
     * Modified Implementation @param string[] $proxyIPs
     * @return string
     * @since 3.0.1
     */
    public function getRequestSourceIP($proxyIPs = [])
    {
        //return $this->ipHelper->detectVisitorIP($proxyIPs);
        $ips = $this->ipHelper->readForwardIpLine();
        $ips = array_diff($ips, $proxyIPs);
        if (empty($ips)) return '0.0.0.0';
        return $ips[0];
    }

    /**
     * @return string
     */
    public function getRequestMethod()
    {
        $method = $this->readServer('REQUEST_METHOD');
        if ($method !== null) {
            return strtoupper($method);
        }
        return ArkHelper::isCLI() ? ArkWebInput::METHOD_CLI : php_sapi_name();
    }

    public function readServer($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SERVER, $name, $default, $regex);
    }

    // shortcut

    /**
     * @param string $fieldName
     * @return int
     * @throws RequestFieldInvalidatedError
     * @throws RequestedFieldMissingError
     * @since 3.4.9
     * @since 3.4.10 Fix for minus number
     */
    public function readIntegerFromRequest(string $fieldName): int
    {
        $x = $this->readIndispensableRequest($fieldName, '/^[+-]?\d+$/');
        return intval($x);
    }

    /**
     * @param string $fieldName
     * @return float
     * @throws RequestFieldInvalidatedError
     * @throws RequestedFieldMissingError
     * @since 3.4.9
     */
    public function readFloatFromRequest(string $fieldName): float
    {
        $x = $this->readIndispensableRequest(
            $fieldName,
            function ($value, $name) {
                return is_numeric($value);
            }
        );
        return floatval($x);
    }
}