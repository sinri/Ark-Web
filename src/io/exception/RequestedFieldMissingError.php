<?php


namespace sinri\ark\io\exception;


use sinri\ark\web\exception\ArkWebRequestFailed;

/**
 * Class RequestedFieldMissingError
 * @package sinri\ark\io\exception
 * @since 3.4.2 as a child class of EnsureItemException
 * @since 3.5.0 as a child class of ArkWebRequestFailed
 */
class RequestedFieldMissingError extends ArkWebRequestFailed
{
    protected $targetFieldName;

    public function __construct(string $field, int $httpStatusCode = 200)
    {
        parent::__construct("Requested field [$field] is missing.", ['field' => $field], $httpStatusCode);
        $this->targetFieldName = $field;
    }

    public function getTargetFieldName()
    {
        return $this->targetFieldName;
    }
}