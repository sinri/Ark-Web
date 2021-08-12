<?php


namespace sinri\ark\io\validator\implement;


use sinri\ark\io\exception\ArkValidatorException;
use sinri\ark\io\validator\ArkWebInputValidateRule;

/**
 * Class ValidateRuleForInteger
 * @package sinri\ark\io\validator\implement
 * @since 3.4.11
 */
class ValidateRuleForInteger extends ArkWebInputValidateRule
{
    public function __construct(array $fieldPath)
    {
        parent::__construct($fieldPath);
    }

    /**
     * NOTE: The `default` would be ignored when `indispensable` is marked.
     * @param int $defaultValue
     * @return ValidateRuleForInteger
     */
    public function default(int $defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @param $inputValue
     * @return int|null
     * @throws ArkValidatorException
     */
    public function getValidatedValue($inputValue)
    {
//        echo 'checking rule for '.json_encode($this->getFieldPath()).' | '.self::class.PHP_EOL;
//        echo "\tagainst value ".json_encode($inputValue).PHP_EOL;

        if (!is_numeric($inputValue)) {
            if ($this->indispensable) {
                throw new ArkValidatorException($this->fieldPath, $inputValue, 'NOT INT', $this->invalidFeedback);
            } else {
                return $this->defaultValue;
            }
        }
        if (1 !== preg_match('/^([+-]?\d+)$/', "$inputValue", $matches)) {
            if ($this->indispensable) {
                throw new ArkValidatorException($this->fieldPath, $inputValue, 'NOT INT', $this->invalidFeedback);
            } else {
                return $this->defaultValue;
            }
        }
        return intval($matches[1]);
    }
}