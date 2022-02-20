<?php

namespace Zenstruck\Collection\Specification\Logic;

use Zenstruck\Collection\Specification\SpecificationInterpreter;

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

    public function __toString(): string
    {
        $children = \array_filter(\array_map([SpecificationInterpreter::class, 'stringify'], $this->children()));

        return \sprintf('%s(%s)', (new \ReflectionClass($this))->getShortName(), \implode(', ', $children));
    }

    /**
     * @return mixed[]
     */
    public function children(): array
    {
        return $this->children;
    }
}
