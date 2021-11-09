<?php

namespace sinri\ark\web\test\web\controller\annotation;

use sinri\ark\web\annotation\definition\ClassForRoute;
use sinri\ark\web\annotation\definition\MethodForRoute;

/**
 * @ClassForRoute(
 *     method="GET",
 *     path="annotation/alpha"
 * )
 */
class WorkerAlpha
{
    /**
     * @MethodForRoute(
     *     method={"GET","POST"},
     *     path="annotation/alpha/go/{p1}"
     * )
     */
    public function go($p1 = 'P1')
    {
        echo __METHOD__ . '(p1=' . $p1 . ')';
    }

    public function come0()
    {
        echo __METHOD__;
    }

    public function come1($p1)
    {
        echo __METHOD__ . '(p1=' . $p1 . ')';
    }

    public function come2($p1, $p2)
    {
        echo __METHOD__ . '(p1=' . $p1 . ',p2=' . $p2 . ')';
    }

    public function come2a($p1, $p2 = 'default2')
    {
        echo __METHOD__ . '(p1=' . $p1 . ',p2=' . $p2 . ')';
    }

    public function come2b($p1 = 'default1', $p2 = 'default2')
    {
        echo __METHOD__ . '(p1=' . $p1 . ',p2=' . $p2 . ')';
    }
}