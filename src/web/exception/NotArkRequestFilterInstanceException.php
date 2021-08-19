<?php


namespace sinri\ark\web\exception;


use Exception;
use Throwable;

/**
 * Class NotArkRequestFilterInstanceException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 */
class NotArkRequestFilterInstanceException extends Exception
{
    /**
     * NotArkRequestFilterInstanceException constructor.
     * @param string $className
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($className = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct("The given class name [$className] is not a subclass of ArkRequestFilter", $code, $previous);
    }

}