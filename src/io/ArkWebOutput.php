<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 16:13
 */

namespace sinri\ark\io;


use sinri\ark\io\traits\ArkWebOutputTrait;

/**
 * Class ArkWebOutput
 * @package sinri\ark\io
 * @since 3.4.2 use trait
 */
class ArkWebOutput
{
    use ArkWebOutputTrait;

    const AJAX_JSON_CODE_OK = "OK";
    const AJAX_JSON_CODE_FAIL = "FAIL";

    const CONTENT_TYPE_JSON = "application/json";
    const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';

    const CHARSET_UTF8 = "UTF-8";
    const CHARSET_GBK = "GBK";

    /**
     * @var ArkWebOutput
     */
    protected static $instance;


    /**
     * @return ArkWebOutput
     */
    public static function getSharedInstance()
    {
        if (self::$instance === null) {
            self::$instance = new ArkWebOutput();
        }
        return self::$instance;
    }


}