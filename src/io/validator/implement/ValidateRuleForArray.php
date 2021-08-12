<?php


namespace sinri\ark\io\validator\implement;


use sinri\ark\io\exception\ArkValidatorException;
use sinri\ark\io\validator\ArkWebInputValidateRule;

class ValidateRuleForArray extends ArkWebInputValidateRule
{
    /**
     * @var ArkWebInputValidateRule
     */
    protected $validateRuleForItem;

    public function __construct(array $fieldPath, ArkWebInputValidateRule $validateRuleForItem)
    {
        parent::__construct($fieldPath);
        $this->validateRuleForItem = $validateRuleForItem;
    }

    /**
     * @return ArkWebInputValidateRule
     */
    public function getValidateRuleForItem(): ArkWebInputValidateRule
    {
        return $this->validateRuleForItem;
    }

    /**
     * @param ArkWebInputValidateRule $validateRuleForItem
     * @return ValidateRuleForArray
     */
    public function setValidateRuleForItem(ArkWebInputValidateRule $validateRuleForItem): ValidateRuleForArray
    {
        $this->validateRuleForItem = $validateRuleForItem;
        return $this;
    }

    public function getValidatedValue($inputValue)
    {
        if (!is_array($inputValue)) {
            throw new ArkValidatorException($this->fieldPath, $inputValue, "NOT AN ARRAY", $this->invalidFeedback);
        }
        $validatedArray = [];
        foreach ($inputValue as $key => $item) {
            $this->validateRuleForItem->fieldPath = array_merge($this->fieldPath, [$key]);
            $validatedArray[$key] = $this->validateRuleForItem->getValidatedValue($item);
        }
        return $validatedArray;
    }
}