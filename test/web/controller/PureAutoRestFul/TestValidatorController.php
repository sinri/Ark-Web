<?php


namespace sinri\ark\web\test\web\controller\PureAutoRestFul;


use sinri\ark\io\ArkWebInput;
use sinri\ark\io\validator\ArkWebInputValidator;
use sinri\ark\io\validator\implement\ValidateRuleForArray;
use sinri\ark\io\validator\implement\ValidateRuleForFloat;
use sinri\ark\io\validator\implement\ValidateRuleForInteger;
use sinri\ark\io\validator\implement\ValidateRuleForMatrix;
use sinri\ark\io\validator\implement\ValidateRuleForNumericString;
use sinri\ark\io\validator\implement\ValidateRuleForStringRegex;
use sinri\ark\web\implement\ArkWebController;
use sinri\ark\web\test\web\entity\ValidatorTestAEntity;

class TestValidatorController extends ArkWebController
{
    public function testA()
    {
        $validator = new ArkWebInputValidator();
        $validator->registerValidateRules(
            [
                (new ValidateRuleForStringRegex(["a1"]))->default("default"),
                (new ValidateRuleForStringRegex(["a2"]))->markIndispensable(),
                (new ValidateRuleForStringRegex(["a3"], '/^\d+$/'))->markIndispensable(),
                (new ValidateRuleForNumericString(["b1"])),
                (new ValidateRuleForNumericString(["b2"]))->default("123.456"),
                (new ValidateRuleForNumericString(["b3"]))->markIndispensable()->setInvalidFeedback("Field b3 should be a numeric string!"),
                (new ValidateRuleForFloat(["c1"])),
                (new ValidateRuleForFloat(["c2"]))->default(1.2),
                (new ValidateRuleForFloat(["c3"]))->markIndispensable()->setInvalidFeedback("c3 wrong"),
                (new ValidateRuleForFloat(["d1"])),
                (new ValidateRuleForFloat(["d2"]))->default(4),
                (new ValidateRuleForFloat(["d3"]))->markIndispensable()->setInvalidFeedback("d3 wrong"),
            ]
        );
        $e = ValidatorTestAEntity::validateWebRequest($validator);
        $this->_sayOK($e->getInputData());
    }

    public function testB()
    {
        $rowValidator = new ArkWebInputValidator();
        $rowValidator->registerValidateRules(
            [
                (new ValidateRuleForInteger([0]))->markIndispensable(),
                (new ValidateRuleForFloat([1]))->markIndispensable(),
                (new ValidateRuleForNumericString([2]))->default('-2.0'),
            ]
        );

        $validator = new ArkWebInputValidator();
        $validator->registerValidateRule(
            (new ValidateRuleForMatrix(['m']))->setValidatorForColumnsInEachRow($rowValidator)
        );

        $matrix = ArkWebInput::getSharedInstance()->getRawPostBodyParsedAsJson();
        $validator->formatArray($matrix);
        $this->_sayOK($matrix);
    }

    public function testC()
    {
        $validator = new ArkWebInputValidator();
        $validator->registerValidateRule(
            new ValidateRuleForArray(
                ['a'],
                (new ValidateRuleForInteger([]))
                    ->markIndispensable()
//                    ->setInvalidFeedback("TEST C Array ITEM SHOULD BE INT!")
            )
        );
        $e = ValidatorTestAEntity::validateWebRequest($validator);
        $this->_sayOK($e->getInputData());
    }
}