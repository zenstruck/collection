<?php

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K of array-key
 * @template V
 *
 * @implements Collection<K,V>
 */
final class ChainCollection implements Collection
{
    /** @use IterableCollection<K,V> */
    use IterableCollection;

    /**
     * @param iterable<Collection<K,V>> $collections
     * @param bool                      $preserveKeys Whether to preserve the keys of the inner collections
     *                                                when iterating.
     *                                                !NOTE! data may be lost when converting to array
     *                                                if inner collections have duplicated keys.
     */
    public function __construct(private iterable $collections, private bool $preserveKeys = false)
    {
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->collections as $collection) {
            if ($this->preserveKeys) {
                yield from $collection;

                continue;
            }

            foreach ($collection as $item) {
                yield $item;
            }
        }
    }

    public function count(): int
    {
        $count = 0;

        foreach ($this->collections as $collection) {
            $count += $collection->count();
        }

        return $count;
    }
}
