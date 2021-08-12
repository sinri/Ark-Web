<?php


namespace sinri\ark\io\validator\implement;


use sinri\ark\io\exception\ArkValidatorException;
use sinri\ark\io\validator\ArkWebInputValidateRule;

/**
 * Class ValidateRuleForFloat
 * @package sinri\ark\io\validator\implement
 * @since 3.4.11
 */
class ValidateRuleForFloat extends ArkWebInputValidateRule
{
    public function __construct(array $fieldPath)
    {
        parent::__construct($fieldPath);
    }

    /**
     * NOTE: The `default` would be ignored when `indispensable` is marked.
     * @param float $defaultValue
     * @return ValidateRuleForFloat
     */
    public function default(float $defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @param mixed $inputValue
     * @return float|null
     * @throws ArkValidatorException
     */
    public function getValidatedValue($inputValue)
    {
        if (!is_numeric($inputValue)) {
            if ($this->indispensable) {
                throw new ArkValidatorException($this->fieldPath, $inputValue, 'NOT FLOAT', $this->invalidFeedback);
            } else {
                return $this->defaultValue;
            }
        }
        return floatval($inputValue);
    }
}