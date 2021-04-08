<?php


namespace sinri\ark\io\exception;


use sinri\ark\core\ArkHelper;
use sinri\ark\core\exception\EnsureItemException;
use Throwable;

/**
 * Class RequestedFieldMissingError
 * @package sinri\ark\io\exception
 * @since 3.4.2
 */
class RequestedFieldMissingError extends EnsureItemException
{
    protected $targetFieldName;

    public function __construct(string $field, Throwable $previous = null)
    {
        parent::__construct("Requested field [{$field}] is missing.", ArkHelper::READ_TARGET_FIELD_NOT_FOUND, $previous);
        $this->targetFieldName = $field;
    }

    public function getTargetFieldName()
    {
        return $this->targetFieldName;
    }
}