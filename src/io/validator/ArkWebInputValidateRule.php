<?php


namespace sinri\ark\io\validator;


use sinri\ark\io\exception\ArkValidatorException;

/**
 * Class ArkWebInputValidateRule
 * @package sinri\ark\io\validator
 * @since 3.4.11
 */
abstract class ArkWebInputValidateRule
{

    /**
     * @var array (int|string)[]
     */
    protected $fieldPath;
    /**
     * @var mixed
     */
    protected $defaultValue;
    /**
     * @var bool
     */
    protected $indispensable;
    /**
     * @var string
     */
    protected $invalidFeedback;

    public function __construct(array $fieldPath)
    {
        $this->fieldPath = $fieldPath;
        $this->indispensable = false;
        $this->defaultValue = null;
        $this->invalidFeedback = '';
    }

    /**
     * @return string
     */
    public function getInvalidFeedback(): string
    {
        return $this->invalidFeedback;
    }

    /**
     * @param string $invalidFeedback
     * @return ArkWebInputValidateRule
     */
    public function setInvalidFeedback(string $invalidFeedback): ArkWebInputValidateRule
    {
        $this->invalidFeedback = $invalidFeedback;
        return $this;
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
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

//    /**
//     * @param $defaultValue
//     * @return ArkWebInputValidateRule
//     */
//    public function setDefaultValue($defaultValue)
//    {
//        $this->defaultValue = $defaultValue;
//        return $this;
//    }

    /**
     * @return bool
     */
    public function isIndispensable(): bool
    {
        return $this->indispensable;
    }

    /**
     * @return ArkWebInputValidateRule
     */
    public function markIndispensable(): ArkWebInputValidateRule
    {
        $this->indispensable = true;
        return $this;
    }

    /**
     * @param mixed $inputValue
     * @return mixed
     * @throws ArkValidatorException
     */
    abstract public function getValidatedValue($inputValue);
}