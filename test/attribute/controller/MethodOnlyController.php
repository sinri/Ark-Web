<?php

namespace sinri\ark\web\test\attribute\controller;

use sinri\ark\web\attribute\MethodForRoute;

class MethodOnlyController
{
    #[MethodForRoute(method: 'GET', path: 'method-only/explicit')]
    public function explicit()
    {
        echo __METHOD__;
    }

    public function ignored()
    {
        echo __METHOD__;
    }

    #[MethodForRoute(method: ['GET', 'POST'], path: 'method-only/multi')]
    public function multiMethod()
    {
        echo __METHOD__;
    }
}