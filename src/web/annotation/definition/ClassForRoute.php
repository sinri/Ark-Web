<?php

namespace sinri\ark\web\annotation\definition;
/**
 * @Annotation
 * @notice it is an experimental function
 * @since 3.5.1
 */
class ClassForRoute
{
    /**
     * @var string|array<string> comma seperated, such as GET,POST
     */
    public string|array $method;
    /**
     * @var string
     */
    public string $path;
    /**
     * @var string|array<string> comma seperated, such as xx\Filter1,xx\Filter2
     */
    public string|array $filters;
    /**
     * @var bool
     */
    public bool $withFreeTail;

    public function __construct()
    {
        $this->method = 'ANY';
        $this->path = '';
        $this->withFreeTail = false;
        $this->filters = '';
    }

    public function getMethods(): array|string
    {
        if (is_array($this->method)) return $this->method;
        return array_filter(explode(',', $this->method));
    }

    public function getFilters(): array|string
    {
        if (is_array($this->filters)) return $this->filters;
        return array_filter(explode(',', $this->filters));
    }
}