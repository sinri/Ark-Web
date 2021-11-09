<?php

namespace sinri\ark\web\test\annotations\entity;

use sinri\ark\web\test\annotations\definition\MethodAnnotationB;
use sinri\ark\web\test\annotations\definition\PropertyAnnotationA;

class SampleClass
{
    /**
     * @PropertyAnnotationA(markA="1")
     * @var string
     */
    public $property;

    /**
     * @MethodAnnotationB(markB="2")
     */
    public function method()
    {

    }
}