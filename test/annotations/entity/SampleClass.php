<?php

namespace sinri\ark\web\test\annotations\entity;

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