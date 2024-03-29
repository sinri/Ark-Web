<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 13:48
 */

namespace sinri\ark\web;

use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\web\exception\ArkFilterRefuseRequestException;
use sinri\ark\web\exception\ArkWebRequestFailed;
use sinri\ark\web\exception\ArkWebRequestMethodUnacceptableException;
use sinri\ark\web\exception\GivenCallbackIsNotCallableException;

/**
 * Interface ArkRouterRule
 * @package sinri\ark\web
 * @since 1.5.0 as interface
 * @since 2.9.0 became abstract class
 */
abstract class ArkRouterRule
{
    /**
     * @var string[] ArkRequestFilter class name list
     */
    protected $filters;
    /**
     * @var bool
     */
    protected $forAnyMethod;
    /**
     * @var string
     * @deprecated since 3.2.0, ArkRouterRule recommends multi-methods
     */
//    protected $method;
    /**
     * @var string[]
     */
    protected $methods;
    /**
     * @var string
     */
    protected $path;
    /**
     * @var callable|string[]
     */
    protected $callback;
    /**
     * @var string
     */
    protected $namespace;
    /**
     * @var string[]
     */
    protected $parsed;

    public function __construct()
    {
//        $this->method = ArkWebInput::METHOD_ANY;
        $this->methods = [];
        $this->path = '';
        $this->callback = function () {
        };
        $this->namespace = '';
        $this->parsed = [];
        $this->filters = [];
    }

    /**
     * @return string[]
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * @param string[] $filters
     * @return ArkRouterRule
     */
    public function setFilters(array $filters): ArkRouterRule
    {
        $this->filters = $filters;
        return $this;
    }

//    /**
//     * @return string
//     * @deprecated since 3.2.0, ArkRouterRule recommends multi-methods
//     */
//    public function getMethod(): string
//    {
//        return $this->method;
//    }

    /**
     * @param string $method
     * @return ArkRouterRule
     * @deprecated since 3.2.0, ArkRouterRule recommends multi-methods
     */
    public function setMethod(string $method): ArkRouterRule
    {
        //$this->method = $method;
        $this->setMethods([$method]);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * @param string[] $methods
     * @return ArkRouterRule
     */
    public function setMethods($methods): ArkRouterRule
    {
        if (in_array(ArkWebInput::METHOD_ANY, $methods)) {
            $this->forAnyMethod = true;
        }
        $this->methods = $methods;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return ArkRouterRule
     */
    public function setPath(string $path): ArkRouterRule
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return callable|string[]
     */
    public function getCallback()
    {
        return $this->callback;
    }

    /**
     * @param callable|string[] $callback
     * @return ArkRouterRule
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     * @return ArkRouterRule
     */
    public function setNamespace(string $namespace): ArkRouterRule
    {
        $this->namespace = $namespace;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getParsed(): array
    {
        return $this->parsed;
    }

    /**
     * @param string[] $parsed
     * @return ArkRouterRule
     */
    public function setParsed(array $parsed): ArkRouterRule
    {
        $this->parsed = $parsed;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return json_encode([
            "type" => $this->getType(),
            //"method" => $this->method,
            "methods" => $this->methods,
            "path" => $this->path,
            "callback" => $this->callback,
            "filters" => $this->filters,
            "namespace" => $this->namespace,
            "parsed" => $this->parsed,
        ]);
    }

    /**
     * A simple but clear string to describe this rule
     * @return string
     * @since 3.1.10
     */
    public function getRulePattern(): string
    {
        $methodsExpression = implode("|", $this->methods);
        $filterTitles = [];
        if (is_array($this->filters)) {
            foreach ($this->filters as $filterClassName) {
                $filterInstance = ArkRequestFilter::makeInstance($filterClassName);
                $filterTitles[] = $filterInstance->filterTitle();
            }
        }
        $filterTitles = implode(' → ', $filterTitles);
        if (0 == strlen($filterTitles)) {
            $filterTitles = 'NO FILTER';
        }
        $callbackString = 'unknown';
        if (is_array(($this->callback))) {
            $callbackString = implode('::', $this->callback);
        } elseif (is_callable($this->callback)) {
            $callbackString = 'anonymous function';
        }
        return $this->getType() . " [$methodsExpression] $this->path : $filterTitles : " . $callbackString;
    }


    /**
     * @param string $className class name with full namespace; use X::CLASS is recommended.
     * @param string $methodName
     */
    public function setCallbackWithClassNameAndMethod($className, $methodName)
    {
        $this->callback = self::buildCallbackDescriptionWithClassNameAndMethod($className, $methodName);
    }

    public static function buildCallbackDescriptionWithClassNameAndMethod($className, $methodName)
    {
        return [$className, $methodName];
    }

//    /**
//     * @param string $method
//     * @param string $path
//     * @param callable|string[] $callback a function with parameters in path, such as `function($post,$comment)` for above
//     * @param string[] $filters ArkRequestFilter class name list
//     * @return ArkRouterRule
//     * @deprecated @since 3.1 why not use constructor?
//     */
//    abstract public static function buildRouteRule($method, $path, $callback, $filters = []);

    /**
     * @param $path_string
     * @param array|mixed $preparedData @since 1.1 this became reference and bug fixed
     * @param int $responseCode @since 1.1 this became reference
     * @throws ArkFilterRefuseRequestException
     * @throws GivenCallbackIsNotCallableException
     * @throws ArkWebRequestFailed since 3.5.0
     * @throws ArkWebRequestMethodUnacceptableException since 3.5.0
     */
    public function execute($path_string, &$preparedData = [], &$responseCode = 200)
    {
        $callable = $this->getCallback();//ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_CALLBACK);
        $params = $this->getParsed();//ArkHelper::readTarget($route, ArkRouter::ROUTE_PARSED_PARAMETERS);
        $filter_chain = $this->getFilters();//ArkHelper::readTarget($route, ArkRouter::ROUTE_PARAM_FILTER);

        self::executeWithFilters($params, $filter_chain, $path_string, $preparedData, $responseCode);
        self::executeWithParameters($callable, $params);
    }

    /**
     * @param $params
     * @param $filter_chain
     * @param $path_string
     * @param array $preparedData
     * @param int $responseCode
     * @throws ArkFilterRefuseRequestException
     */
    protected static function executeWithFilters($params, $filter_chain, $path_string, &$preparedData = [], &$responseCode = 200)
    {
        if (!is_array($filter_chain)) {
            $filter_chain = [$filter_chain];
        }
        foreach ($filter_chain as $filter) {
            $filter_instance = ArkRequestFilter::makeInstance($filter);
            $shouldAcceptRequest = $filter_instance->shouldAcceptRequest(
                $path_string,
                //Ark()->webInput()
                ArkWebInput::getSharedInstance()
                    ->getRequestMethod(),
                $params,
                $preparedData,
                $responseCode,
                $filterError
            );
            if (!$shouldAcceptRequest) {
                throw new ArkFilterRefuseRequestException(
                    $filterError,
                    $filter_instance->filterTitle(),
                    $responseCode
                );
            }
        }
    }

    /**
     * The parameter callable could be:
     * - (anonymous) function
     * - [class_name, method_name] -> (new class_name())->method_name
     * - [class_instance, method_name] -> class_instance->method_name @param string[]|callable $callable
     * @param array $params
     * @throws GivenCallbackIsNotCallableException
     * @throws ArkWebRequestFailed @since 3.4.12
     * @since 3.4.5
     *
     * i.e. Static Methods are not supported here.
     *
     */
    protected static function executeWithParameters($callable, $params)
    {
        if (is_array($callable)) {
            if (count($callable) !== 2) {
                throw new GivenCallbackIsNotCallableException($callable);
            }
            if (is_string($callable[0])) {
                $class_instance_name = $callable[0];
                $class_instance = new $class_instance_name();
                $callable[0] = $class_instance;
            }
        }
        call_user_func_array($callable, $params);
    }

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @param string $incomingPath
     * @return false|string
     */
    protected function preprocessIncomingPath(string $incomingPath)
    {
        $path = $incomingPath;// as is for static
        if (strlen($incomingPath) > 1 && substr($incomingPath, strlen($incomingPath) - 1, 1) == '/') {
            $path = substr($incomingPath, 0, strlen($incomingPath) - 1);// this should be cut for non-static route rule
        } elseif ($incomingPath == '') {
            $path = '/'; // fulfill as no leading `/`
        }
        return $path;
    }

    protected function checkIfMatchMethod(string $method)
    {
        return $this->forAnyMethod || in_array($method, $this->methods);
    }

    /**
     * @param string $method
     * @param string $incomingPath
     * @param null|ArkLogger $logger
     * @return boolean
     */
    public function checkIfMatchRequest($method, $incomingPath, $logger = null)
    {
        if ($logger) {
            $logger->debug(
                __METHOD__ . '@' . __LINE__ . ' this rule: ' . $this->__toString(),
                [
                    'req_method' => $method,
                    'req_incoming_path' => $incomingPath,
                ]
            );
        }
        if (!$this->checkIfMatchMethod($method)) {
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . " Rule Method Matched Fails");
            }
            return false;
        }
        $path = $this->preprocessIncomingPath($incomingPath);
        $route_regex = $this->getPath();

        if (preg_match($route_regex, $path, $matches)) {
            if (!empty($matches)) array_shift($matches);
            $matches = array_filter($matches, function ($v) {
                return substr($v, 0, 1) != '/';
            });
            $matches = array_values($matches);
            array_walk($matches, function (&$v) {
                $v = urldecode($v);
            });
            $this->setParsed($matches);
            if ($logger) {
                $logger->debug(__METHOD__ . '@' . __LINE__ . " MATCHED with " . json_encode($matches));
            }
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isForAnyMethod(): bool
    {
        return $this->forAnyMethod;
    }

    /**
     * @param bool $forAnyMethod
     * @return ArkRouterRule
     */
    public function setForAnyMethod(bool $forAnyMethod)
    {
        $this->forAnyMethod = $forAnyMethod;
        return $this;
    }
}