<?php
/**
 * Created by PhpStorm.
 * User: Sinri
 * Date: 2018/2/13
 * Time: 23:22
 */

namespace sinri\ark\web\test\web\controller;


use Exception;
use sinri\ark\core\ArkHelper;
use sinri\ark\io\ArkWebInput;
use sinri\ark\web\implement\ArkWebController;

class Foo extends ArkWebController
{
    public function bar($a, $b = 'B')
    {
        $this->_sayOK([
            'a' => $a,
            'b' => $b,
            'token' => ArkWebInput::getSharedInstance()->readRequest('token'),
            'time' => ArkHelper::readTarget($this->filterGeneratedData, ['request_time'], 'unknown'),
        ]);
    }

    public function tail($a, $b, $tail = [])
    {
        $this->_sayOK([
            'a' => $a,
            'b' => $b,
            'tail' => $tail,
        ]);
    }

    /**
     * @throws Exception
     */
    public function error()
    {
        throw new Exception("miao", 666);
    }
}