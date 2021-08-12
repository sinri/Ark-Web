<?php


namespace sinri\ark\io\exception;


use Exception;
use Throwable;

/**
 * Class ArkValidatorException
 * @package sinri\ark\io\exception
 * @since 3.4.11
 */
class ArkValidatorException extends Exception
{
    /**
     * @var array
     */
    protected $fieldPath;
    /**
     * @var mixed
     */
    protected $inputValue;
    /**
     * @var string
     */
    protected $feedback;
    /**
     * @var string
     */
    protected $reason;

    public function __construct(array $fieldPath, $inputValue, string $reason, string $feedback = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct(
            strlen($feedback) > 0 ? $feedback : self::makeCauseString($fieldPath, $inputValue, $reason),
            $code,
            $previous
        );
        $this->fieldPath = $fieldPath;
        $this->inputValue = $inputValue;
        $this->feedback = $feedback;
        $this->reason = $reason;
    }

    protected static function makeCauseString(array $fieldPath, $inputValue, string $reason): string
    {
        return "Validator found the value (" . json_encode($inputValue) . ") "
            . "of field " . json_encode($fieldPath) . " "
            . "is not correct, reason: " . $reason;
    }

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    public function getCause(): string
    {
        return self::makeCauseString($this->fieldPath, $this->inputValue, $this->reason);
    }

    /**
     * @return string
     */
    public function getFeedback(): string
    {
        return $this->feedback;
    }

    /**
     * @return array
     */
    public function getFieldPath(): array
    {
        return $this->fieldPath;
    }

    /**
     * @return mixed
     */
    public function getInputValue()
    {
        return $this->inputValue;
    }
}