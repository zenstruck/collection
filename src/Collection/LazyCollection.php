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
    use IterableCollection;

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

        $this->source = \is_array($source) ? new \ArrayIterator($source) : $source; // @phpstan-ignore assign.propertyType
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
