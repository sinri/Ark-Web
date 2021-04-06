<?php


namespace sinri\ark\io\exception;


use Exception;
use Throwable;

/**
 * Class IPFormatError
 * @package sinri\ark\io\exception
 * @since 3.4.2
 */
class IPFormatError extends Exception
{
    protected $ip;

    public function __construct($ip = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("IP Expression [{$ip}] is not valid.", $code, $previous);
        $this->ip = $ip;
    }

    /**
     * @return string
     */
    public function getIP(): string
    {
        return $this->ip;
    }


}