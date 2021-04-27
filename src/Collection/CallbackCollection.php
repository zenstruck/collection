<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackCollection implements Collection
{
    use Paginatable;

    private IterableCollection $iterator;
    private $count;

    public function __construct(callable $iterator, callable $count)
    {
        $this->iterator = new IterableCollection($iterator);
        $this->count = $count;
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        return $this->iterator->take($limit, $offset);
    }

    public function getIterator(): \Traversable
    {
        return $this->iterator;
    }

    public function count(): int
    {
        return ($this->count)();
    }
}
