<?php

namespace sinri\ark\web\exception;

use RuntimeException;
use Throwable;

/**
 * @since 3.5.0
 */
class ArkWebRequestFailed extends RuntimeException
{
    /**
     * @var array
     */
    protected $detail;

    public function __construct(string $message, array $detail = [], int $httpStatusCode = 200, Throwable $previous = null)
    {
        parent::__construct($message, $httpStatusCode, $previous);
        $this->detail = $detail;
    }

    /**
     * @return array
     */
    public function getDetail(): array
    {
        return $this->detail;
    }
}