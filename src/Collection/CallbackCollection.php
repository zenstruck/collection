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
    /** @use IterableCollection<K,V> */
    use IterableCollection;

    /** @var LazyCollection<K,V> */
    private LazyCollection $iterator;
    private \Closure $count;

    public function __construct(callable $iterator, callable $count)
    {
        $this->iterator = new LazyCollection($iterator);
        $this->count = \Closure::fromCallable($count);
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
