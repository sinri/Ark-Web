<?php


namespace sinri\ark\web\exception;


use Exception;

/**
 * Class ArkFilterRefuseRequestException
 * @package sinri\ark\web\exception
 * @since 3.4.2
 * @since 3.5.0 make code default as 403
 */
class ArkFilterRefuseRequestException extends Exception
{
    protected $filterError;
    protected $filterTitle;

    public function __construct($filterError = "", $filterTitle = '', $code = 403)
    {
        parent::__construct("The request is rejected by [$filterTitle], reason: " . $filterError, $code);
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