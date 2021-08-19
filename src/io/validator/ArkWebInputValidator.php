<?php


namespace sinri\ark\io\validator;


use sinri\ark\core\ArkHelper;
use sinri\ark\io\ArkWebInput;
use sinri\ark\io\exception\ArkValidatorException;

/**
 * Class ArkWebInputValidator
 * @package sinri\ark\io\validator
 * @since 3.4.11V
 */
class ArkWebInputValidator
{
    /**
     * @var ArkWebInputValidateRule[]
     */
    protected $validateRuleList;

    public function __construct()
    {
        $this->validateRuleList = [];
    }

    public function registerValidateRule(ArkWebInputValidateRule $validateRule)
    {
        $this->validateRuleList[] = $validateRule;
        return $this;
    }

    /**
     * @param ArkWebInputValidateRule[] $validateRules
     * @return $this
     */
    public function registerValidateRules(array $validateRules)
    {
        $this->validateRuleList = array_merge($this->validateRuleList, $validateRules);
        return $this;
    }

    /**
     * @param ArkWebInputValidatedEntity|null $validated
     * @return ArkWebInputValidatedEntity
     * @throws ArkValidatorException
     * @since 3.5.0 parameter `$validated` might be omitted, and it might be automatically generated, and be returned.
     */
    public function validateWebRequest(ArkWebInputValidatedEntity $validated = null)
    {
        if ($validated === null) {
            $validated = new ArkWebInputValidatedEntity();
        }
        foreach ($this->validateRuleList as $validateRule) {
            $inputValue = ArkWebInput::getSharedInstance()->readRequest($validateRule->getFieldPath());
            $validatedValue = $validateRule->getValidatedValue($inputValue);
            $validated->write($validateRule->getFieldPath(), $validatedValue);
        }
        return $validated;
    }

    /**
     * @param ArkWebInputValidatedEntity|null $validated
     * @return ArkWebInputValidatedEntity
     * @throws ArkValidatorException
     * @since 3.5.0 parameter `$validated` might be omitted, and it might be automatically generated, and be returned.
     */
    public function validateWebPost(ArkWebInputValidatedEntity $validated = null)
    {
        if ($validated === null) {
            $validated = new ArkWebInputValidatedEntity();
        }
        foreach ($this->validateRuleList as $validateRule) {
            $inputValue = ArkWebInput::getSharedInstance()->readPost($validateRule->getFieldPath());
            $validatedValue = $validateRule->getValidatedValue($inputValue);
            $validated->write($validateRule->getFieldPath(), $validatedValue);
        }
        return $validated;
    }

    /**
     * @param scalar[] $inputArray One Dimension Array
     * @throws ArkValidatorException
     */
    public function formatArray(array &$inputArray)
    {
        foreach ($this->validateRuleList as $validateRule) {
//            echo 'checking rule for '.json_encode($validateRule->getFieldPath()).' | '.get_class($validateRule).PHP_EOL;
            $subInputValue = ArkHelper::readTarget($inputArray, $validateRule->getFieldPath());
//            echo "\t"."against value ".json_encode($subInputValue).PHP_EOL;
            $validatedValue = $validateRule->getValidatedValue($subInputValue);
//            echo "\t"."validated as ".json_encode($validatedValue).PHP_EOL;
            ArkHelper::writeIntoArray($inputArray, $validateRule->getFieldPath(), $validatedValue);
        }
    }
}