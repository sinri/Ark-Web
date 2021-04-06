<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:51
 */

namespace sinri\ark\web;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;

class ArkWebService
{
    /**
     * @var ArkWebService
     */
    protected static $instance;
    /**
     * @var bool
     */
    protected $debug;
    /**
     * @var ArkLogger
     */
    protected $logger;
    /**
     * @var ArkRouter
     */
    protected $router;
    /**
     * @var string
     */
    protected $requestSerial;
    /**
     * @var float
     * @since 3.2.3
     * @since 3.4.1 from int to float
     */
    protected $startTime;
    /**
     * @var float
     * @since 3.2.3
     * @since 3.4.1 from int to float
     */
    protected $endTime;
    /**
     * @var callable
     */
    protected $finalHandler;
    /**
     * @var string
     */
    protected $gateway;
    /**
     * @var null|array|mixed
     * Till 3.2.2 it was named as `filterGeneratedData`
     * @since 3.2.3
     */
    protected $sharedData;
    /**
     * @var string
     * @since 3.4.1
     */
    protected $currentRequestPath;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->endTime = -1;
        $this->requestSerial = uniqid();
        $this->gateway = "index.php";
        $this->logger = ArkLogger::makeSilentLogger();
        $this->debug = false;
        $this->router = new ArkRouter();
        $this->sharedData = null;
    }

    /**
     * @return ArkWebService
     */
    public static function getSharedInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ArkWebService();
        }
        return self::$instance;
    }

    /**
     * @return string
     */
    public function getCurrentRequestPath(): string
    {
        return $this->currentRequestPath;
    }

    /**
     * @return float
     * @since 3.4.1 from int to float
     */
    public function getStartTime(): float
    {
        return $this->startTime;
    }

    /**
     * @return float
     * @since 3.4.1 from int to float
     */
    public function getEndTime(): float
    {
        return $this->endTime;
    }

    /**
     * @return float The seconds, from start to end (or now, if not ended yet)
     * @since 3.4.1
     */
    public function getRequestProcessedSeconds(): float
    {
        if ($this->endTime > 0) {
            return $this->endTime - $this->startTime;
        } else {
            return microtime(true) - $this->startTime;
        }
    }

    /**
     * @param callable $finalHandler
     * @return ArkWebService
     */
    public function setFinalHandler(callable $finalHandler): ArkWebService
    {
        $this->finalHandler = $finalHandler;
        return $this;
    }

    /**
     * @return string
     */
    public function getRequestSerial(): string
    {
        return $this->requestSerial;
    }

    /**
     * @return ArkRouter
     */
    public function getRouter(): ArkRouter
    {
        return $this->router;
    }

    /**
     * @return null
     * @since 3.2.3 renamed from `getFilterGeneratedData`
     */
    public function getSharedData()
    {
        return $this->sharedData;
    }

    /**
     * @return null
     * @deprecated since 3.2.3
     */
    public function getFilterGeneratedData()
    {
        return $this->sharedData;
    }

    /**
     * @param bool $debug
     * @return ArkWebService
     */
    public function setDebug(bool $debug)
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param string $gateway
     * @return ArkWebService
     */
    public function setGateway(string $gateway)
    {
        $this->gateway = $gateway;
        return $this;
    }

    /**
     * @param ArkLogger $logger
     * @return ArkWebService
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * This is commonly a final call after other configurations
     */
    public function handleRequest()
    {
        if (ArkHelper::isCLI()) {
            $this->handleRequestForCLI();
            return;
        }
        $this->handleRequestForWeb();
    }

    /**
     * This is commonly a final call after other configurations
     */
    public function handleRequestForCLI()
    {
        global $argc;
        global $argv;
        try {
            // php index.php [PATH] [ARGV]
            $path = ArkHelper::readTarget($argv, 1);
            if ($path === null) {
                $this->logger->error("PATH EMPTY", [$path]);
                return;
            }
            $arguments = [];
            for ($i = 2; $i < $argc; $i++) {
                $arguments[] = $argv[$i];
            }
            $this->currentRequestPath = $path;
            $route = $this->router->seekRoute(
                $this->currentRequestPath,
                //Ark()->webInput()->getRequestMethod()
                ArkWebInput::getSharedInstance()->getRequestMethod()
            );
            $code = 0;
            $route->setParsed($arguments);
            $route->execute($this->currentRequestPath, $this->sharedData, $code);
        } catch (Exception $exception) {
            $this->logger->error("Exception in " . __METHOD__ . " : " . $exception->getMessage());
        } finally {
            $this->endTime = microtime(true);
            if (is_callable($this->finalHandler)) {
                call_user_func_array($this->finalHandler, []);
            }
        }
    }

    /**
     * This is commonly a final call after other configurations
     */
    public function handleRequestForWeb()
    {
        try {
            $this->dividePath($this->currentRequestPath);
            $route = $this->router->seekRoute(
                $this->currentRequestPath,
                //Ark()->webInput()->getRequestMethod()
                ArkWebInput::getSharedInstance()->getRequestMethod()
            );
            $code = 200;
            $route->execute($this->currentRequestPath, $this->sharedData, $code);
        } catch (Exception $exception) {
            $this->router->handleRouteError(
                [
                    'exception_code' => $exception->getCode(),
                    'exception_message' => $exception->getMessage(),
                ],
                $exception->getCode()
            );
            if ($this->debug) {
                echo "<pre>" . PHP_EOL . print_r($exception, true) . "</pre>" . PHP_EOL;
            }
        } finally {
            $this->endTime = microtime(true);
            if (is_callable($this->finalHandler)) {
                call_user_func_array($this->finalHandler, []);
            }
        }
    }

    /**
     * @param string $pathString It would be as output.
     * @return string[] array Array of components
     */
    protected function dividePath(&$pathString = '')
    {
        $sub_paths = array();
        if (ArkHelper::isCLI()) {
            global $argv;
            global $argc;
            for ($i = 1; $i < $argc; $i++) {
                $sub_paths[] = $argv[$i];
            }
            return $sub_paths;
        }

        $fullPathString = $this->fetchControllerPathString();
        $tmp = explode('?', $fullPathString);
        $pathString = isset($tmp[0]) ? $tmp[0] : '';
        $pattern = '/^\/([^\?]*)(\?|$)/';
        $r = preg_match($pattern, $pathString, $matches);
        if (!$r) {
            // https://github.com/sinri/enoch/issues/1
            // this bug (return '' which is not an array) fixed since v1.0.2
            return [''];
        }
        $controller_array = explode('/', $matches[1]);
        if (count($controller_array) > 0) {
            $sub_paths = array_filter($controller_array, function ($var) {
                return $var !== '';
            });
            $sub_paths = array_values($sub_paths);
        }

        return $sub_paths;
    }

    protected function fetchControllerPathString()
    {
        $prefix = $_SERVER['SCRIPT_NAME'];
        //$delta=10;//original
        $delta = strlen($this->gateway) + 1;

        if (
            (strpos($_SERVER['REQUEST_URI'], $prefix) !== 0)
            && (strrpos($prefix, '/' . $this->gateway) + $delta == strlen($prefix))
        ) {
            $prefix = substr($prefix, 0, strlen($prefix) - $delta);
        }

        return substr($_SERVER['REQUEST_URI'], strlen($prefix));
    }

    /**
     * If you decide to use PHP Session, please run this before the code to handle request.
     * @param $sessionDir
     * @return ArkWebService
     * @deprecated Session could be implemented in many ways, this is not a good name. use startPHPSession instead.
     */
    public function startSession($sessionDir)
    {
        return $this->startPHPSession($sessionDir);
    }

    /**
     * @param string $sessionDir
     * @return ArkWebService
     */
    public function startPHPSession($sessionDir)
    {
        ArkWebSession::sessionStart($sessionDir);
        return $this;
    }

    /**
     * @param string $pathPrefix no leading and tail '/'
     * @param string $baseDirPath
     * @param ArkRequestFilter[] $filters
     * @param null|callable $fileHandler e.g. function($realPath,$components):void if null, execute default downloader @since 2.7.2 added a secondary parameter in 2.8.1
     * @param null|callable $dirHandler e.g. function($realPath,$components):void if null, use default HTML display @since 2.8.1
     * @return ArkRouter @since 2.8.1
     *
     * @since 2.7.1
     *
     * Set up a quick readonly FTP-like file system viewer,
     * binding a uri path prefix to a file system path prefix.
     *
     */
    public function setupFileSystemViewer($pathPrefix, $baseDirPath, $filters = [], $fileHandler = null, $dirHandler = null)
    {
        return $this->router->get($pathPrefix, function ($components) use ($dirHandler, $fileHandler, $pathPrefix, $baseDirPath) {
            if (count($components) === 1 && $components[0] === '') {
                $components = [];
            }
            $baseDirPath = realpath($baseDirPath);
            if ($baseDirPath === false) {
                throw new Exception("Resource Configuration Error!", 500);
            }
            $rawPath = $baseDirPath . (empty($components) ? "" : "/" . implode("/", $components));
            $realPath = realpath($rawPath);
            if ($realPath === false || $realPath !== $rawPath) {
                throw new Exception("Illegal Resource Index!", 400);
            }
            if (!file_exists($realPath)) {
                throw new Exception("Resource Not Found!", 404);
            }
            if (is_dir($realPath)) {
                // if dir path not ends with / add one to its tail
                $parts = explode("?", $_SERVER['REQUEST_URI']);
                $path = $parts[0];
                if (substr($path, strlen($path) - 1, 1) !== '/') {
                    header("Location: " . $path . '/' . (count($parts) > 1 ? "?" . $parts[1] : ""));
                    return;
                }

                if (is_callable($dirHandler)) {
                    call_user_func_array($dirHandler, [$realPath, $components]);
                } else {
                    //Ark()->webOutput()
                    ArkWebOutput::getSharedInstance()
                        ->displayPage(__DIR__ . '/template/FileSystemViewerDirPageTemplate.php', [
                        'components' => $components,
                        'realPath' => $realPath,
                    ]);
                }
            } else {
                // show content
                if (is_callable($fileHandler)) {
                    call_user_func_array($fileHandler, [$realPath, $components]);
                } else {
                    ArkWebOutput::getSharedInstance()
                        ->downloadFileIndirectly($realPath);
                }
            }
        }, $filters, true);

    }
}