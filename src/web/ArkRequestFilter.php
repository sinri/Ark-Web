<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 20:57
 */

namespace sinri\ark\web;


use sinri\ark\web\exception\NotArkRequestFilterInstanceException;

abstract class ArkRequestFilter
{
    /**
     * @param string $class_name string class name with namespace
     * @return ArkRequestFilter
     */
    public static function makeInstance(string $class_name): ArkRequestFilter
    {
        if (!empty($class_name)) {
            try {
                // @since 3.1.10 check class type
                // @since 3.4.9 simplify logic
                if (!is_a($class_name, self::class, true)) {
                    throw new NotArkRequestFilterInstanceException($class_name);
                }
                return new $class_name();
            } catch (NotArkRequestFilterInstanceException $exception) {
                return self::generateFilter(true, 'Exception in requiring a filter: ' . $exception->getMessage(), 500);
            }
        }
        // if not given, just make an anonymous implementation
        return self::generateFilter(false, '', 200);
    }

    /**
     * @param boolean $shouldDeny
     * @param string $injectError
     * @param int $injectCode
     * @return ArkRequestFilter
     */
    protected static function generateFilter(bool $shouldDeny, string $injectError, int $injectCode): ArkRequestFilter
    {
        return new class($shouldDeny, $injectError, $injectCode) extends ArkRequestFilter {
            protected bool $shouldDeny;
            protected string $injectError;
            protected int $injectCode;

            /**
             *  constructor.
             * @param boolean $shouldDeny
             * @param string $injectError
             * @param int $injectCode
             */
            public function __construct(bool $shouldDeny, string $injectError, int $injectCode)
            {
                $this->shouldDeny = $shouldDeny;
                $this->injectError = $injectError;
                $this->injectCode = $injectCode;
            }

            public function shouldAcceptRequest($path, $method, $params, mixed &$preparedData = null, int &$responseCode = 200, ?string &$error = null): bool
            {
                $responseCode = $this->injectCode;
                $error = $this->injectError;//"The Ark is locked now.";
                return !$this->shouldDeny;
            }

            /**
             * Give filter a name for Error Report
             * @return string
             */
            public function filterTitle(): string
            {
                return "Auto-Generated Ark Filter";
            }
        };
    }

    /**
     * Check request data with $_REQUEST, $_SESSION, $_SERVER, etc.
     * And decide if the request should be accepted.
     * If return false, the request would be thrown.
     * You can pass anything into $preparedData, that controller might use it (not sure, by the realization)
     * @param $path
     * @param $method
     * @param $params
     * @param mixed $preparedData
     * @param int $responseCode
     * @param string|null $error
     * @return bool
     */
    abstract public function shouldAcceptRequest($path, $method, $params, mixed &$preparedData = null, int &$responseCode = 200, ?string &$error = null): bool;

    /**
     * Give filter a name for Error Report
     * @return string
     */
    abstract public function filterTitle(): string;

    /**
     * You can use this as `hasPrefixAmong($path,['/AdminSession/login','/FileAgent/getFile/'])`
     * as the paths like '/AdminSession/login' and '/FileAgent/getFile/xxx' would return true.
     * Only pure string (case sensitive) would be taken as check rule.
     * Anyway, you may think about the shared prefix, such as '/AdminSession/loginAgain', returns true too.
     * But you should know, all the urls are designed by yourself, you can design them to avoid side effects.
     * @param string $path
     * @param array $prefixList
     * @return bool
     */
    public static function hasPrefixAmong(string $path, array $prefixList = []): bool
    {
        foreach ($prefixList as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }
        return false;
    }

}