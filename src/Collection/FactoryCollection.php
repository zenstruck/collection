<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FactoryCollection implements Collection
{
    use Paginatable;

    private Collection $inner;
    private $factory;

    public function __construct(Collection $collection, callable $factory)
    {
        $this->inner = $collection;
        $this->factory = $factory;
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        return new self($this->inner->take($limit, $offset), $this->factory);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->inner as $key => $value) {
            yield $key => ($this->factory)($value);
        }
    }

    public function count(): int
    {
        return $this->inner->count();
    }
}
