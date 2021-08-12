<?php


namespace sinri\ark\io\validator\implement;


use sinri\ark\io\exception\ArkValidatorException;
use sinri\ark\io\validator\ArkWebInputValidateRule;

/**
 * Class ValidateRuleForStringRegex
 * @package sinri\ark\io\validator\implement
 * @since 3.4.11
 */
class ValidateRuleForStringRegex extends ArkWebInputValidateRule
{
    protected $regex;

    public function __construct(array $fieldPath, string $regex = null)
    {
        parent::__construct($fieldPath);
        $this->regex = $regex;
    }

    /**
     * NOTE: The `default` would be ignored when `indispensable` is marked.
     * @param string $defaultValue
     * @return ValidateRuleForStringRegex
     */
    public function default(string $defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @param mixed $inputValue
     * @return string|null
     * @throws ArkValidatorException
     */
    public function getValidatedValue($inputValue)
    {
        if (!is_string($inputValue) && !is_numeric($inputValue)) {
            if ($this->indispensable) {
                throw new ArkValidatorException($this->fieldPath, $inputValue, "NOT STRING", $this->invalidFeedback);
            } else {
                return $this->defaultValue;
            }
        }

        $inputValue = "$inputValue";

        if (is_string($this->regex)) {
            if (preg_match($this->regex, $inputValue, $matches) !== 1) {
                if ($this->indispensable) {
                    throw new ArkValidatorException($this->fieldPath, $inputValue, "STRING DOES NOT MATCH REGEX", $this->invalidFeedback);
                } else {
                    return $this->defaultValue;
                }
            }
            return $matches[0];
        }
        return $inputValue;
    }
}