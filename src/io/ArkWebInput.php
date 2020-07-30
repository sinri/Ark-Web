<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 15:02
 */

namespace sinri\ark\io;


use Exception;
use sinri\ark\core\ArkHelper;

class ArkWebInput
{
    const METHOD_ANY = "ANY";//since v2.1.3 for TreeRouter

    const METHOD_HEAD = "HEAD";//since v1.3.0
    const METHOD_GET = "GET";//since v1.3.0
    const METHOD_POST = "POST";//since v1.3.0
    const METHOD_PUT = "PUT";//since v1.3.0
    const METHOD_DELETE = "DELETE";//since v1.3.0
    const METHOD_OPTIONS = "OPTIONS";//since v1.3.0
    const METHOD_PATCH = "PATCH";//since v1.3.0
    const METHOD_CLI = "cli";//since v1.3.0

    protected $headerHelper;
    protected $ipHelper;
    protected $uploadFileHelper;
    protected $rawPostBody;
    protected $rawPostBodyParsedAsJson;

    /**
     * @var ArkWebInput
     */
    protected static $instance;

    /**
     * @return ArkWebInput
     */
    public static function getSharedInstance(){
        if(self::$instance===null){
            self::$instance=new ArkWebInput();
        }
        return self::$instance;
    }

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
     * @param string|array $name
     * @param null|mixed $default
     * @param null|string $regex
     * @param null|Exception $error
     * @return mixed
     */
    public function readRequest($name, $default = null, $regex = null, &$error = null)
    {
        $value = ArkHelper::readTarget($_REQUEST, $name, $default, $regex, $error);
        try {
            $content_type = $this->headerHelper->getHeader("CONTENT-TYPE", null, '/^application\/json/');
            if (
                $content_type !== null
                //preg_match('/^application\/json(;.+)?$/', $content_type)
            ) {
                if (is_array($this->rawPostBodyParsedAsJson)) {
                    $value = ArkHelper::readTarget($this->rawPostBodyParsedAsJson, $name, $default, $regex, $error);
                }
            }
        } catch (Exception $exception) {
            // actually do nothing.
            $error = $exception;
        }
        return $value;
    }

    /**
     * @param string $name
     * @param callable|string|null $checker An anonymous function `f(value,name)` or a regular expression, else would not check any more
     * @return mixed
     * @throws Exception
     * @since 2.6
     * @since 2.8.1 add a secondary parameter to checker when it is a callable function
     */
    public function readIndispensableRequest($name, $checker = null)
    {
        $value = $this->readRequest($name);
        if ($value === null) {
            throw new Exception("Field [$name] is missing!", ArkHelper::READ_TARGET_FIELD_NOT_FOUND);
        }
        if (is_callable($checker)) {
            if (!call_user_func_array($checker, [$value, $name])) {
                throw new Exception("Field [$name] format error with check function!", ArkHelper::READ_TARGET_REGEX_NOT_MATCH);
            }
        } elseif (is_string($checker) && strlen($checker) > 2) {
            // at least it would be have `/` in head and tail
            if (!preg_match($checker, $value)) {
                throw new Exception("Field [$name] format error with check regex!", ArkHelper::READ_TARGET_REGEX_NOT_MATCH);
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

    public function readServer($name, $default = null, $regex = null)
    {
        return ArkHelper::readTarget($_SERVER, $name, $default, $regex);
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
            $method = strtoupper($method);
            return $method;
        }
        return ArkHelper::isCLI() ? self::METHOD_CLI : php_sapi_name();
    }

}