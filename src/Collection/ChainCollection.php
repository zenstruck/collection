<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template K
 * @template V
 * @implements Collection<K,V>
 */
final class ChainCollection implements Collection
{
    /** @use IterableCollection<K,V> */
    use IterableCollection;

    /** @var Collection<int,Collection<K,V>> */
    private Collection $collections;

    /**
     * @param iterable<Collection<K,V>> $collections
     * @param bool                      $preserveKeys Whether to preserve the keys of the inner collections
     *                                                when iterating.
     *                                                !NOTE! data may be lost when converting to array
     *                                                if inner collections have duplicated keys.
     */
    public function __construct(iterable $collections, private bool $preserveKeys = false)
    {
        $this->collections = $collections instanceof Collection ? $collections : new LazyCollection($collections);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->collections as $collection) {
            if ($this->preserveKeys) {
                yield from $collection;

                continue;
            }

            foreach ($collection as $item) {
                yield $item; // @phpstan-ignore generator.keyType
            }
        }
    }

    public function count(): int
    {
        return $this->collections->reduce(fn(int $r, Collection $c) => $r + $c->count(), 0); // @phpstan-ignore return.type
    }
}
