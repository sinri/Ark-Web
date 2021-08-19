<?php


namespace sinri\ark\io\exception;


use sinri\ark\web\exception\ArkWebRequestFailed;

/**
 * Class RequestFieldInvalidatedError
 * @package sinri\ark\io\exception
 * @since 3.4.2 as a child class of EnsureItemException
 * @since 3.5.0 as a child class of ArkWebRequestFailed
 */
class RequestFieldInvalidatedError extends ArkWebRequestFailed
{
    protected $targetFieldName;
    protected $valueProvided;

    public function __construct(string $field, $value = null, int $httpStatusCode = 200)
    {
        parent::__construct("Request field [$field] could not be validated.", ['field' => $field, 'value' => $value], $httpStatusCode);
        $this->targetFieldName = $field;
        $this->valueProvided = $value;
    }

    /**
     * @return string
     */
    public function getTargetFieldName(): string
    {
        return $this->targetFieldName;
    }

    /**
     * @return mixed
     */
    public function getValueProvided()
    {
        return $this->valueProvided;
    }
}