<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 17:58
 */

namespace sinri\ark\web;


use Exception;
use Psr\Log\LogLevel;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use sinri\ark\core\ArkLogger;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\ArkWebOutput;
use sinri\ark\web\attribute\ClassForRoute;
use sinri\ark\web\attribute\MethodForRoute;
use sinri\ark\web\exception\ArkRouteNoMatchedException;
use sinri\ark\web\implement\ArkRouteErrorHandlerAsJson;
use sinri\ark\web\implement\ArkRouterAutoRestfulRule;
use sinri\ark\web\implement\ArkRouterFreeTailRule;
use sinri\ark\web\implement\ArkRouterRestfulRule;
use sinri\ark\web\implement\ArkRouterStaticRule;

class ArkRouter
{
    /**
     * @var bool
     */
    protected bool $debug;
    /**
     * @var ArkLogger
     */
    protected ArkLogger $logger;
    protected ?string $defaultControllerName = null;
    protected ?string $defaultMethodName = null;
    /**
     * @var ArkRouteErrorHandlerInterface|null
     */
    protected ?ArkRouteErrorHandlerInterface $errorHandler = null;

    /**
     * @var ArkRouterRule[]
     */
    protected array $rules;

    public function __construct()
    {
        $this->debug = false;
        $this->logger = ArkLogger::makeSilentLogger();
        $this->defaultControllerName = 'Welcome';
        $this->defaultMethodName = 'index';
        $this->errorHandler = null;
        $this->rules = [];
    }

    /**
     * @param bool $debug
     * @return ArkRouter
     */
    public function setDebug(bool $debug): static
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param ArkLogger $logger
     * @return ArkRouter
     */
    public function setLogger(ArkLogger $logger): static
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * @param null|string $defaultControllerName
     * @return ArkRouter
     */
    public function setDefaultControllerName(?string $defaultControllerName): static
    {
        $this->defaultControllerName = $defaultControllerName;
        return $this;
    }

    /**
     * @param null|string $defaultMethodName
     * @return ArkRouter
     */
    public function setDefaultMethodName(?string $defaultMethodName): static
    {
        $this->defaultMethodName = $defaultMethodName;
        return $this;
    }

    /**
     * Give a string as a template file path for display-page use;
     * give an anonymous function or a callable definition array which consume one parameter of array,
     * or leave it as null to response JSON.
     * @param ArkRouteErrorHandlerInterface|null $errorHandler
     * @return ArkRouter
     */
    public function setErrorHandler(?ArkRouteErrorHandlerInterface $errorHandler): static
    {
        $this->errorHandler = $errorHandler;
        return $this;
    }

    /**
     * @param Exception $error
     * @param int $httpCode @since 1.2.8
     * @since 3.4.8 $errorData renamed to $error, and its type might be Exception now
     */
    public function handleRouteError(Exception $error, int $httpCode): void
    {
        if (!$this->errorHandler) {
            $this->errorHandler = new ArkRouteErrorHandlerAsJson();
        }
        $this->errorHandler->execute($error, $httpCode);
        // @since 3.5.0 moved to router from service
        if ($this->debug) {
            echo "<pre>" . PHP_EOL . print_r($error, true) . "</pre>" . PHP_EOL;
        }
    }

    /**
     * @param ArkRouterRule $routeRule
     * @return $this
     */
    public function registerRouteRule(ArkRouterRule $routeRule): static
    {
        array_unshift($this->rules, $routeRule);
        return $this;
    }

    /**
     * @param string $path
     * @param string $dir
     * @param string[] $filters
     * @return ArkRouter
     */
    public function registerFrontendFolder(string $path, string $dir, array $filters = []): static
    {
        $staticRule = new ArkRouterStaticRule(
            [ArkWebInput::METHOD_ANY],
            $path,
            function ($subPath = null) use ($dir) {
                if ($subPath === null || $subPath === '') {
                    $subPath = 'index.html';
                }
                $path = $dir . '/' . $subPath;
                //Ark()->webOutput()
                ArkWebOutput::getSharedInstance()
                    ->downloadFileIndirectly($path);
            },
            $filters
        );
        //$this->registerStaticRouteRule($staticRule);
        $this->registerRouteRule($staticRule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function get(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_GET], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_GET], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function post(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_POST], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_POST], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function put(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_PUT], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_PUT], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function patch(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_PATCH], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_PATCH], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function delete(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_DELETE], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_DELETE], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function options(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_OPTIONS], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_OPTIONS], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function head(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_HEAD], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_HEAD], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string $path `posts/{post}/comments/{comment}` no leading `/`
     * @param callable|array $callback a function with parameters in path, such as `function($post,$comment)` for above
     * @param string[] $filters ArkRequestFilter class name list
     * @param bool $hasFreeTail
     * @return ArkRouter
     */
    public function any(string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule([ArkWebInput::METHOD_ANY], $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_ANY], $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        return $this;
    }

    /**
     * @param string[] $methods array of ArkWebInput::METHOD_*
     * @param string $path
     * @param callable|array $callback
     * @param string[] $filters
     * @param bool $hasFreeTail
     * @return ArkRouter
     * @since 2.9.2
     * @since 3.2.0 It seems, not so useful now...
     */
    public function multiMethods(array $methods, string $path, callable|array $callback, array $filters = [], bool $hasFreeTail = false): static
    {
        //foreach ($methods as $method) {
        if ($hasFreeTail) {
            $route_rule = new ArkRouterFreeTailRule($methods, $path, $callback, $filters);
        } else {
            $route_rule = new ArkRouterRestfulRule($methods, $path, $callback, $filters);
        }
        $this->registerRouteRule($route_rule);
        //}
        return $this;
    }

    /**
     * @param string $incomingPath
     * @param string $method
     * @return ArkRouterRule
     * @throws ArkRouteNoMatchedException
     */
    public function seekRoute(string $incomingPath, string $method): ArkRouterRule
    {
        foreach ($this->rules as $rule) {
            $matched = $rule->checkIfMatchRequest($method, $incomingPath, (($this->debug) ? $this->logger : null));
            if ($this->debug) {
                $context = @json_decode($rule->__toString(), true);
                $context['incoming_path'] = $incomingPath;
                $this->logger->smartLog(
                    $matched,
                    'Route Rule Matched!', $context,
                    'Route Rule Did not match!', $context,
                    LogLevel::DEBUG,
                    LogLevel::DEBUG
                );
            }
            if ($matched) {
                return $rule;
            }
        }
        throw new ArkRouteNoMatchedException($method, $incomingPath);
    }

    /**
     * @return string[]
     * @since 3.1.10
     */
    public function getListOfRouteRules(): array
    {
        $rulePatternList = [];
        foreach ($this->rules as $rule) {
            $rulePatternList[] = $rule->getRulePattern();
        }
        return $rulePatternList;
    }

//    /**
//     * @param ArkRouterRestfulRule $shared
//     * @param ArkRouterRestfulRule[] $list
//     * @return ArkRouter
//     * @deprecated since 2.10 It should not be used anymore
//     */
//    public function group($shared, $list)
//    {
//        $filters = null;
//        $sharedPath = '';
//        $sharedNamespace = '';
//
//        if ($shared->getFilters() !== null) {
//            $filters = $shared->getFilters();
//        }
//        if ($shared->getPath() !== null) {
//            $sharedPath = $shared->getPath();
//        }
//        if ($shared->getNamespace() !== null) {
//            $sharedNamespace = $shared->getNamespace();
//        }
//
//        foreach ($list as $item) {
//            $callback = $item->getCallback();
//            if (is_array($callback) && isset($callback[0]) && is_string($callback[0])) {
//                $callback[0] = $sharedNamespace . $callback[0];
//            }
//            $route_rule = new ArkRouterRestfulRule(
//                $item->getMethod(),//$item[self::ROUTE_PARAM_METHOD],
//                $sharedPath . $item->getPath(),//$item[self::ROUTE_PARAM_PATH],
//                $callback,
//                $filters
//            );
//            $this->registerRouteRule($route_rule);
////            $this->registerRestfulRouteRule($route_rule);
//        }
//        return $this;
//    }

    /**
     * @param string $urlBase 'xx/'
     * @param string $namespace '\a\b\c' without tail; X::class is also supported @since 3.2.1
     * @param string[] $filters array of class name
     * @return $this
     * @since 3.1.0
     */
    public function loadAutoRestfulControllerRoot(string $urlBase, string $namespace, array $filters = []): static
    {
        $this->registerRouteRule(
            new ArkRouterAutoRestfulRule(
                [ArkWebInput::METHOD_ANY],
                $urlBase,
                $namespace,
                $filters
            )
        );
        return $this;
    }

    /**
     * @param string $directory __DIR__ . '/../controller'
     * @param string $urlBase "XX/"
     * @param string $controllerNamespaceBase '\sinri\sample\controller\\'
     * @param string[] $filters ['\sinri\sample\filter\AuthFilter']
     * @return ArkRouter
     */
    public function loadAllControllersInDirectoryAsCI(string $directory, string $urlBase = '', string $controllerNamespaceBase = '', array $filters = []): static
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            if ($this->debug) {
                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " warning: this is not a directory: " . $directory);
            }
        } elseif ($handle = opendir($directory)) {
            if (
                $this->defaultControllerName
                && file_exists($directory . '/' . $this->defaultControllerName . '.php')
                && $this->defaultMethodName
                && method_exists($controllerNamespaceBase . $this->defaultControllerName, $this->defaultMethodName)
            ) {
                $this->any(
                    $urlBase . '?',
                    [$controllerNamespaceBase . $this->defaultControllerName, $this->defaultMethodName],
                    $filters
                );
            }
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir($directory . '/' . $entry)) {
                        //DIR,
                        $this->loadAllControllersInDirectoryAsCI(
                            $directory . '/' . $entry,
                            $urlBase . $entry . '/',
                            $controllerNamespaceBase . $entry . '\\',
                            $filters
                        );
                    } else {
                        //FILE
                        $list = explode('.', $entry);
                        $name = $list[0] ?? '';
                        if (
                            $this->defaultMethodName
                            && method_exists($controllerNamespaceBase . $name, $this->defaultMethodName)
                        ) {
                            $this->any(
                                $urlBase . $name . '/?',
                                [$controllerNamespaceBase . $name, $this->defaultMethodName],
                                $filters
                            );
                        }
                        $this->loadController(
                            $urlBase . $name . '/',
                            $controllerNamespaceBase . $name,
                            $filters
                        );
                    }
                }
            }
            closedir($handle);
        }
        return $this;
    }

    /**
     * @param string $basePath "" or "xx/"
     * @param string $controllerClass full class describer
     * @param string[] $filters
     * @return ArkRouter
     */
    public function loadController(string $basePath, string $controllerClass, array $filters = []): static
    {
        try {
            $method_list = get_class_methods($controllerClass);
            $reflector = new ReflectionClass($controllerClass);
            foreach ($method_list as $method) {
                if (str_starts_with($method, '_')) {
                    continue;
                }
                $path = $basePath . $method;
                $parameters = $reflector->getMethod($method)->getParameters();
                $after_string = "";
                $came_in_default_area = false;
                if (!empty($parameters)) {
                    foreach ($parameters as $param) {
                        if ($param->isDefaultValueAvailable()) {
                            $path .= "(";
                            $after_string .= ")?";
                            $came_in_default_area = true;
                        } elseif ($came_in_default_area) {
                            //non-default after default
                            if ($this->debug) {
                                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " ROUTE SETTING ERROR: required-parameter after non-required-parameter");
                            }
                            return $this;
                        }
                        $path .= '/{' . $param->name . '}';
                    }
                    $path .= $after_string;
                }
                $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_ANY], $path, [$controllerClass, $method], $filters);
//                $this->registerRouteRule($route_rule);
                $this->registerRouteRule($route_rule);
                if ($method == $this->defaultMethodName) {
                    $basePathX = $basePath;
                    if (strlen($basePathX) > 0) {
                        $basePathX = substr($basePathX, 0, strlen($basePathX) - 1);
                    }
                    $route_rule = new ArkRouterRestfulRule([ArkWebInput::METHOD_ANY], $basePathX, [$controllerClass, $method], $filters);
//                    $this->registerRouteRule($route_rule);
                    $this->registerRouteRule($route_rule);
                }
            }
        } catch (ReflectionException) {
            // do nothing if class not exist
        }
        return $this;
    }

    /**
     * @param string $directory __DIR__ . '/../controller'
     * @param string $controllerNamespaceBase '\sinri\sample\controller\\'
     * @return $this
     * @throws ReflectionException
     *
     * ClassForRoute と MethodForRoute という標記をしたコントローラークラスとその中のメソッドを自動的にルートを登録。
     * @notice it is an experimental function
     * @since 3.5.1
     */
    public function loadRoutesWithAttributeInDirectory(string $directory, string $controllerNamespaceBase = ''): static
    {
        if (!file_exists($directory) || !is_dir($directory)) {
            if ($this->debug) {
                $this->logger->debug(__METHOD__ . '@' . __LINE__ . " warning: this is not a directory: " . $directory);
            }
        } elseif ($handle = opendir($directory)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    if (is_dir($directory . '/' . $entry)) {
                        $this->loadRoutesWithAttributeInDirectory(
                            $directory . '/' . $entry,
                            $controllerNamespaceBase . $entry . '\\'
                        );
                    } else {
                        $list = explode('.', $entry);
                        $controllerClassName = $list[0] ?? '';

                        $reflectionClass = new ReflectionClass($controllerNamespaceBase . $controllerClassName);
                        $classForRoute = $this->getClassAttribute($reflectionClass, ClassForRoute::class);

                        $reflectionClassMethodList = $reflectionClass->getMethods();
                        foreach ($reflectionClassMethodList as $reflectionMethod) {
                            if (!$reflectionMethod->isPublic()) {
                                continue;
                            }
                            $methodForRoute = $this->getMethodAttribute($reflectionMethod, MethodForRoute::class);
                            if ($methodForRoute !== null) {
                                $this->multiMethods(
                                    $methodForRoute->getMethods(),
                                    $methodForRoute->path,
                                    [$controllerNamespaceBase . $controllerClassName, $reflectionMethod->getName()],
                                    $methodForRoute->getFilters(),
                                    $methodForRoute->withFreeTail
                                );
                                continue;
                            }
                            if ($classForRoute !== null) {
                                $min = $reflectionMethod->getNumberOfRequiredParameters();
                                $max = $reflectionMethod->getNumberOfParameters();
                                $pathTail = '';
                                for ($i = 0; $i <= $max; $i++) {
                                    if ($i >= $min) {
                                        $this->multiMethods(
                                            $classForRoute->getMethods(),
                                            $classForRoute->path . '/' . $reflectionMethod->getName() . $pathTail,
                                            [$controllerNamespaceBase . $controllerClassName, $reflectionMethod->getName()],
                                            $classForRoute->getFilters(),
                                            $classForRoute->withFreeTail
                                        );
                                    }
                                    if ($i < $max) {
                                        $pathTail .= '/{' . $reflectionMethod->getParameters()[$i]->getName() . '}';
                                    }
                                }
                            }
                        }
                    }
                }
            }
            closedir($handle);
        }
        return $this;
    }

    /**
     * @deprecated use loadRoutesWithAttributeInDirectory() instead
     */
    public function loadRoutesWithAnnotationInDirectory(string $directory, string $controllerNamespaceBase = ''): static
    {
        return $this->loadRoutesWithAttributeInDirectory($directory, $controllerNamespaceBase);
    }

    /**
     * @template T of object
     * @param ReflectionClass $reflectionClass
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    private function getClassAttribute(ReflectionClass $reflectionClass, string $attributeClass): ?object
    {
        $attributes = $reflectionClass->getAttributes($attributeClass);
        if ($attributes === []) {
            return null;
        }
        return $attributes[0]->newInstance();
    }

    /**
     * @template T of object
     * @param ReflectionMethod $reflectionMethod
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    private function getMethodAttribute(ReflectionMethod $reflectionMethod, string $attributeClass): ?object
    {
        $attributes = $reflectionMethod->getAttributes($attributeClass);
        if ($attributes === []) {
            return null;
        }
        return $attributes[0]->newInstance();
    }
}
