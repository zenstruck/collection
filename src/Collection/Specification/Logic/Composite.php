<?php

namespace Zenstruck\Collection\Specification\Logic;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Composite
{
    private array $children;

    public function __construct(...$children)
    {
        $this->children = $children;
    }

    public function children(): array
    {
        return $this->children;
    }
}
