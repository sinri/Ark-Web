<?php


namespace sinri\ark\web\exception;


use Exception;
use Throwable;

/**
 * Class ArkFilterRefuseRequestException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 */
class ArkFilterRefuseRequestException extends Exception
{
    protected $filterError;
    protected $filterTitle;

    public function __construct($filterError = "", $filterTitle = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct("The request is rejected by [{$filterTitle}], reason: " . $filterError, $code, $previous);
        $this->filterTitle = $filterTitle;
        $this->filterError = $filterError;
    }

    /**
     * @return string
     */
    public function getFilterError(): string
    {
        return $this->filterError;
    }

    /**
     * @return string
     */
    public function getFilterTitle(): string
    {
        return $this->filterTitle;
    }
}