<?php

namespace sinri\ark\web\test\attribute\controller;

use sinri\ark\web\attribute\ClassForRoute;
use sinri\ark\web\attribute\MethodForRoute;
use sinri\ark\web\test\web\filter\TestFilter;

#[ClassForRoute(method: 'GET', path: 'filtered/class', filters: [TestFilter::class])]
class WithFiltersController
{
    public function list()
    {
        echo __METHOD__;
    }

    #[MethodForRoute(method: 'POST', path: 'filtered/method', filters: [TestFilter::class])]
    public function create()
    {
        echo __METHOD__;
    }
}