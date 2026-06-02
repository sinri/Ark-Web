<?php

namespace sinri\ark\web\attribute;

use Attribute;

/**
 * @notice it is an experimental function
 * @since 3.5.1
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class ClassForRoute
{
    public function __construct(
        public string|array $method = 'ANY',
        public string $path = '',
        public string|array $filters = '',
        public bool $withFreeTail = false,
    ) {
    }

    public function getMethods(): array
    {
        if (is_array($this->method)) {
            return $this->method;
        }
        return array_filter(explode(',', $this->method));
    }

    public function getFilters(): array
    {
        if (is_array($this->filters)) {
            return $this->filters;
        }
        return array_filter(explode(',', $this->filters));
    }
}
