<?php

namespace Zenstruck\Collection\Specification\Logic;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Composite
{
    /** @var mixed[] */
    private array $children;

    public function __construct(mixed ...$children)
    {
        $this->children = $children;
    }

    /**
     * @return mixed[]
     */
    public function children(): array
    {
        return $this->children;
    }
}
