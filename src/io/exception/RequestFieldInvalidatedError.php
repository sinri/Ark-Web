<?php


namespace sinri\ark\io\exception;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\exception\EnsureItemException;
use Throwable;

/**
 * Class RequestFieldInvalidatedError
 * @package sinri\ark\io\exception
 * @since 3.4.2
 */
class RequestFieldInvalidatedError extends EnsureItemException
{
    protected $targetFieldName;
    protected $valueProvided;

    public function __construct(string $field, $value = null, Throwable $previous = null)
    {
        parent::__construct("Request field [{$field}] could not be validated.", ArkHelper::READ_TARGET_REGEX_NOT_MATCH, $previous);
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