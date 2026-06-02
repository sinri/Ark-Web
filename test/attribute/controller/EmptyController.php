<?php

namespace sinri\ark\web\test\attribute\controller;

use sinri\ark\web\attribute\ClassForRoute;

#[ClassForRoute]
class EmptyController
{
    public function hello()
    {
        echo __METHOD__;
    }
}