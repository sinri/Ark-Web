<?php


namespace sinri\ark\web\test\web\controller\PureAutoRestFul;


use sinri\ark\web\implement\ArkWebController;

class JustSingleController extends ArkWebController
{
    public function work()
    {
        echo __METHOD__;
    }
}