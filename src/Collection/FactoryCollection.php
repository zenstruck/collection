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
 * @template V
 * @template K = array-key
 * @implements Collection<V,K>
 */
final class FactoryCollection implements Collection
{
    /** @use IterableCollection<V,K> */
    use IterableCollection;

    /** @var Collection<mixed,K> */
    private Collection $inner;
    private \Closure $factory;

    /**
     * @template T
     *
     * @param Collection<T,K> $collection
     * @param callable(T):V   $factory
     */
    public function __construct(Collection $collection, callable $factory)
    {
        $this->inner = $collection;
        $this->factory = $factory(...);
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
