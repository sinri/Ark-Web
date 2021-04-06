<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 15:02
 */

namespace sinri\ark\io;


use sinri\ark\io\traits\ArkWebInputTrait;

/**
 * Class ArkWebInput
 * @package sinri\ark\io
 * @since 3.4.2 Use trait
 */
class ArkWebInput
{
    use ArkWebInputTrait;

    const METHOD_ANY = "ANY";//since v2.1.3 for TreeRouter

    const METHOD_HEAD = "HEAD";//since v1.3.0
    const METHOD_GET = "GET";//since v1.3.0
    const METHOD_POST = "POST";//since v1.3.0
    const METHOD_PUT = "PUT";//since v1.3.0
    const METHOD_DELETE = "DELETE";//since v1.3.0
    const METHOD_OPTIONS = "OPTIONS";//since v1.3.0
    const METHOD_PATCH = "PATCH";//since v1.3.0
    const METHOD_CLI = "cli";//since v1.3.0

    /**
     * @var ArkWebInput
     */
    protected static $instance;

    /**
     * @return ArkWebInput
     */
    public static function getSharedInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ArkWebInput();
        }
        return self::$instance;
    }
}