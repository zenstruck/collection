<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 * @implements Collection<K,V>
 */
final class CallbackCollection implements Collection
{
    /** @use Paginatable<V> */
    use Paginatable;

    /** @var IterableCollection<K,V> */
    private IterableCollection $iterator;
    private \Closure $count;

    public function __construct(callable $iterator, callable $count)
    {
        $this->iterator = new IterableCollection($iterator);
        $this->count = \Closure::fromCallable($count);
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
