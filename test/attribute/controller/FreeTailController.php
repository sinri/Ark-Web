<?php

namespace sinri\ark\web\test\attribute\controller;

use sinri\ark\web\attribute\ClassForRoute;
use sinri\ark\web\attribute\MethodForRoute;

#[ClassForRoute(method: 'GET', path: 'free/class', withFreeTail: true)]
class FreeTailController
{
    public function show()
    {
        echo __METHOD__;
    }

    #[MethodForRoute(method: 'POST', path: 'free/method/{id}', withFreeTail: true)]
    public function store($id)
    {
        echo __METHOD__ . '(id=' . $id . ')';
    }
}