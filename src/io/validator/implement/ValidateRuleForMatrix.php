<?php


namespace sinri\ark\io\validator\implement;


use InvalidArgumentException;
use sinri\ark\io\exception\ArkValidatorException;
use sinri\ark\io\validator\ArkWebInputValidateRule;
use sinri\ark\io\validator\ArkWebInputValidator;

/**
 * Class ValidateRuleForMatrix
 * @package sinri\ark\io\validator\implement
 * @since 3.4.11
 */
class ValidateRuleForMatrix extends ArkWebInputValidateRule
{
    /**
     * @var ArkWebInputValidator
     */
    protected $validatorForColumnsInEachRow;

    /**
     * ArkWebInputValidateRuleForMatrix constructor.
     * @param array $fieldPath
     */
    public function __construct(array $fieldPath)
    {
        parent::__construct($fieldPath);
    }

    /**
     * @return ArkWebInputValidator
     */
    public function getValidatorForColumnsInEachRow(): ArkWebInputValidator
    {
        return $this->validatorForColumnsInEachRow;
    }

    /**
     * @param ArkWebInputValidator $validatorForColumnsInEachRow
     * @return ValidateRuleForMatrix
     */
    public function setValidatorForColumnsInEachRow(ArkWebInputValidator $validatorForColumnsInEachRow): ValidateRuleForMatrix
    {
        $this->validatorForColumnsInEachRow = $validatorForColumnsInEachRow;
        return $this;
    }

    /**
     * NOTE: The `default` would be ignored when `indispensable` is marked.
     * @param array[] $defaultValue
     * @return ValidateRuleForMatrix
     */
    public function default(array $defaultValue)
    {
        foreach ($defaultValue as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException("DEFAULT MUST BE A MATRIX");
            }
        }
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @param mixed $inputValue
     * @return array|null
     * @throws ArkValidatorException
     */
    public function getValidatedValue($inputValue)
    {
//        echo 'checking rule for '.json_encode($this->getFieldPath()).' | '.self::class.PHP_EOL;
//        echo "\t"."against value ".json_encode($inputValue).PHP_EOL;

        // the input value is an array as a matrix
        if (!is_array($inputValue)) {
            if ($this->indispensable) {
                throw new ArkValidatorException($this->fieldPath, $inputValue, 'NOT A MATRIX', $this->invalidFeedback);
            } else {
                return $this->defaultValue;
            }
        }

        $matrix = [];

        foreach ($inputValue as $index => $row) {
            if (!is_array($row)) {
                if ($this->indispensable) {
                    throw new ArkValidatorException($this->fieldPath, $inputValue, 'ITEM NOT ARRAY', $this->invalidFeedback);
                } else {
                    return $this->defaultValue;
                }
            }

            try {
                $matrix[$index] = $row;
                $this->validatorForColumnsInEachRow->formatArray($matrix[$index]);
            } catch (ArkValidatorException $e) {
                $mergedFieldPath = array_merge($this->getFieldPath(), $e->getFieldPath());
                if (strlen($e->getFeedback()) > 0) {
                    if (strlen($this->invalidFeedback) > 0) {
                        $feedback = $this->invalidFeedback . "(" . $e->getFeedback() . ")";
                    } else {
                        $feedback = $e->getFeedback();
                    }
                } else {
                    $feedback = $this->invalidFeedback;
                }
                throw new ArkValidatorException(
                    $mergedFieldPath,
                    $e->getInputValue(),
                    $e->getReason(),
                    $feedback
                );
            }
        }

        return $matrix;
    }

//    /**
//     * For a matrix, maybe huge, so it directly
//     * @param mixed $inputValue
//     * @return array|null
//     * @throws ArkValidatorException
//     */
//    public function getValidatedValueRaw($inputValue)
//    {
////        echo 'checking rule for '.json_encode($this->getFieldPath()).' | '.self::class.PHP_EOL;
////        echo "\t"."against value ".json_encode($inputValue).PHP_EOL;
//
//        // the input value is an array as a matrix
//        if (!is_array($inputValue)) {
//            if ($this->indispensable) {
//                throw new ArkValidatorException($this->fieldPath, $inputValue, 'NOT A MATRIX', $this->invalidFeedback);
//            } else {
//                return $this->defaultValue;
//            }
//        }
//        foreach ($inputValue as &$row) {
//            if (!is_array($row)) {
//                if ($this->indispensable) {
//                    throw new ArkValidatorException($this->fieldPath, $inputValue, 'ITEM NOT ARRAY', $this->invalidFeedback);
//                } else {
//                    return $this->defaultValue;
//                }
//            }
//
//            try {
//                $this->validatorForColumnsInEachRow->formatArray($row);
//            } catch (ArkValidatorException $e) {
//                $mergedFieldPath=array_merge($this->getFieldPath(),$e->getFieldPath());
//                if(strlen($e->getFeedback())>0){
//                    if(strlen($this->invalidFeedback)>0){
//                        $feedback=$this->invalidFeedback."(".$e->getFeedback().")";
//                    }else{
//                        $feedback=$e->getFeedback();
//                    }
//                }else{
//                    $feedback=$this->invalidFeedback;
//                }
//                throw new ArkValidatorException(
//                    $mergedFieldPath,
//                    $e->getInputValue(),
//                    $e->getReason(),
//                    $feedback
//                );
//            }
//        }
//
//        return $inputValue;
//    }
}