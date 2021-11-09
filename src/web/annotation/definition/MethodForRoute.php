<?php

namespace sinri\ark\web\annotation\definition;
/**
 * @Annotation
 * @notice it is an experimental function
 * @since 3.5.1
 */
final class MethodForRoute
{
    /**
     * @var string|array<string> comma seperated, such as GET,POST
     */
    public $method;
    /**
     * @var string
     */
    public $path;
    /**
     * @var string|array<string> comma seperated, such as xx\Filter1,xx\Filter2
     */
    public $filters;
    /**
     * @var bool
     */
    public $withFreeTail;

    public function __construct()
    {
        $this->method = 'ANY';
        $this->path = '';
        $this->withFreeTail = false;
        $this->filters = '';
    }

    public function getMethods()
    {
        if (is_array($this->method)) return $this->method;
        return array_filter(explode(',', $this->method));
    }

    public function getFilters()
    {
        if (is_array($this->filters)) return $this->filters;
        return array_filter(explode(',', $this->filters));
    }
}