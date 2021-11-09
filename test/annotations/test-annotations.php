<?php

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use sinri\ark\web\test\annotations\definition\MethodAnnotationB;
use sinri\ark\web\test\annotations\definition\PropertyAnnotationA;
use sinri\ark\web\test\annotations\entity\SampleClass;

require_once __DIR__ . '/../../vendor/autoload.php';

// Deprecated and will be removed in 2.0 but currently needed
AnnotationRegistry::registerLoader('class_exists');

$reflectionClass = new ReflectionClass(SampleClass::class);
$property = $reflectionClass->getProperty('property');

$reader = new AnnotationReader();
$propertyAnnotation = $reader->getPropertyAnnotation($property, PropertyAnnotationA::class);
echo $propertyAnnotation->markA . PHP_EOL; // result: "1"

$method = $reflectionClass->getMethod('method');
$methodAnnotation = $reader->getMethodAnnotation($method, MethodAnnotationB::class);
echo $methodAnnotation->markB . PHP_EOL; // result: "2"
