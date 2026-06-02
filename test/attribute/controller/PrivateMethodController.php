<?php

namespace sinri\ark\web\test\attribute\controller;

use sinri\ark\web\attribute\ClassForRoute;
use sinri\ark\web\attribute\MethodForRoute;

#[ClassForRoute(method: 'GET', path: 'private')]
class PrivateMethodController
{
    #[MethodForRoute(method: 'GET', path: 'private/visible')]
    public function visible()
    {
        echo __METHOD__;
    }

    private function hidden()
    {
        echo __METHOD__;
    }

    #[MethodForRoute(method: 'GET', path: 'private/hidden-attr')]
    private function hiddenWithAttr()
    {
        echo __METHOD__;
    }
}