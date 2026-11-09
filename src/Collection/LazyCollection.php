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
final class LazyCollection implements Collection
{
    /** @use IterableCollection<V,K> */
    use IterableCollection {
        take as private innerTake;
    }

    /** @var \Traversable<K,V>|\Closure():iterable<K,V> */
    private \Closure|\Traversable $source;

    /**
     * @param iterable<K,V>|callable():iterable<K,V> $source
     */
    public function __construct(iterable|callable $source = [])
    {
        if ($source instanceof \Generator) {
            throw new \InvalidArgumentException('$source must not be a generator directly as generators cannot be rewound. Try wrapping in a closure.');
        }

        if (\is_callable($source) && (!\is_iterable($source) || \is_array($source))) {
            $source = $source(...); // @phpstan-ignore callable.nonCallable
        }

        $this->source = \is_array($source) ? new \ArrayIterator($source) : $source;
    }

    /**
     * @return self<V,K>
     */
    public function take(int $limit, int $offset = 0): self
    {
        if (!$this->source instanceof \Closure || $limit < 0 || $offset < 0) {
            return $this->innerTake($limit, $offset);
        }

        if (0 === $limit) {
            return new self();
        }

        return new self(fn() => $this->takeFromCallback($limit, $offset));
    }

    /**
     * @return \Traversable<K,V>
     */
    private function takeFromCallback(int $limit, int $offset): \Traversable
    {
        $source = $this->iterableSource();
        $slice = self::sliceArraySource($source, $limit, $offset);

        return $slice ?? self::iterateSlice($source, $limit, $offset);
    }

    /**
     * @return iterable<K,V>
     */
    private function iterableSource(): iterable
    {
        if ($this->source instanceof \Traversable) {
            return $this->source;
        }

        // source is callback
        $source = ($this->source)();

        if ($source instanceof \Generator) {
            // generators cannot be rewound so don't set as $source (ensure callback is executed next time)
            return $source;
        }

        if (!\is_iterable($source)) {
            throw new \InvalidArgumentException('$source callback must return iterable.');
        }

        return $this->source = \is_array($source) ? new \ArrayIterator($source) : $source;
    }
}
