<?php


namespace sinri\ark\io\validator;


use sinri\ark\core\ArkHelper;
use sinri\ark\io\exception\ArkValidatorException;

/**
 * Class ArkWebInputValidatedEntity
 * @package sinri\ark\io\validator
 * @since 3.4.11
 */
class ArkWebInputValidatedEntity
{
    /**
     * @var array
     */
    private $inputData;

    public function __construct()
    {
        $this->inputData = [];
    }

    /**
     * @param ArkWebInputValidator $validator
     * @return static
     * @throws ArkValidatorException
     */
    public static function validateWebRequest(ArkWebInputValidator $validator)
    {
        $x = new static();
        $validator->validateWebRequest($x);
        return $x;
    }

    /**
     * @return array
     */
    public function getInputData(): array
    {
        return $this->inputData;
    }

    public function __isset($name)
    {
        if (is_scalar($name)) return isset($this->inputData[$name]);
        return (null === ArkHelper::readTarget($this->inputData, $name));
    }

    public function __get($name)
    {
        return ArkHelper::readTarget($this->inputData, $name);
    }

    public function __set($name, $value)
    {
        ArkHelper::writeIntoArray($this->inputData, $name, $value);
    }

    public function read(array $fieldPath)
    {
        return ArkHelper::readTarget($this->inputData, $fieldPath);
    }

    public function write(array $fieldPath, $value)
    {
        ArkHelper::writeIntoArray($this->inputData, $fieldPath, $value);
        return $this;
    }
}