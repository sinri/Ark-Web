<?php


namespace sinri\ark\io\validator\implement;


use InvalidArgumentException;
use sinri\ark\io\exception\ArkValidatorException;
use sinri\ark\io\validator\ArkWebInputValidateRule;

/**
 * Class ValidateRuleForNumericString
 * @package sinri\ark\io\validator\implement
 * @since 3.4.11
 */
class ValidateRuleForNumericString extends ArkWebInputValidateRule
{
    /**
     * ArkWebInputValidateRuleForNumericString constructor.
     * @param array $fieldPath
     */
    public function __construct(array $fieldPath)
    {
        parent::__construct($fieldPath);

    }

    /**
     * NOTE: The `default` would be ignored when `indispensable` is marked.
     * @param numeric-string $defaultValue
     * @return ValidateRuleForNumericString
     */
    public function default(string $defaultValue)
    {
        $this->defaultValue = $defaultValue;
        if (!is_numeric($defaultValue)) {
            throw new InvalidArgumentException("DEFAULT MUST BE A Numeric String");
        }
        return $this;
    }

    /**
     * @param mixed $inputValue
     * @return numeric-string|null
     * @throws ArkValidatorException
     */
    public function getValidatedValue($inputValue)
    {
        if (!is_numeric($inputValue)) {
            if ($this->indispensable) {
                throw new ArkValidatorException($this->fieldPath, $inputValue, "NOT NUMERIC", $this->invalidFeedback);
            } else {
                return $this->defaultValue;
            }
        }
        return "$inputValue";
    }
}