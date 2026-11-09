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
    use IterableCollection {
        take as private innerTake;
    }

    /** @var Collection<mixed,K> */
    private Collection $inner;
    private \Closure $factory;

    /**
     * @template T
     *
     * @param Collection<T,K> $collection
     * @param ($passKey is true ? (callable(T,K):V) : (callable(T):V)) $factory
     * @param bool $passKey Whether to pass the item key as the factory's second argument.
     */
    public function __construct(Collection $collection, callable $factory, bool $passKey = false)
    {
        $this->inner = $collection;
        $factory = $factory(...);
        $this->factory = $passKey ? $factory : static fn(mixed $value) => $factory($value);
    }

    public function getIterator(): \Traversable
    {
        return $this->mapItems($this->inner);
    }

    /**
     * @return LazyCollection<V,K>
     */
    public function take(int $limit, int $offset = 0): Collection
    {
        if ($limit <= 0 || $offset <= 0) {
            return $this->innerTake($limit, $offset);
        }

        return new LazyCollection(function() use ($limit, $offset) {
            // Slice the iterator because the source's take() may renumber its keys.
            $slice = self::iterateSlice($this->inner, $limit, $offset);

            return $this->mapItems($slice);
        });
    }

    public function count(): int
    {
        return $this->inner->count();
    }

    public function isEmpty(): bool
    {
        return $this->inner->isEmpty();
    }

    /**
     * @param iterable<K,mixed> $items
     *
     * @return \Generator<K,V>
     */
    private function mapItems(iterable $items): \Generator
    {
        foreach ($items as $key => $value) {
            yield $key => ($this->factory)($value, $key);
        }
    }
}
