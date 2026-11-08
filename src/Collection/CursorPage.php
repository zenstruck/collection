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
 * @template K of array-key = array-key
 * @implements \IteratorAggregate<K,V>
 */
final class CursorPage implements \IteratorAggregate, \Countable
{
    /**
     * @param Collection<V,K> $items the items already selected for this page
     */
    public function __construct(
        private readonly Collection $items,
        private readonly ?string $nextCursor = null,
        private readonly ?string $previousCursor = null,
    ) {
    }

    public function getIterator(): \Traversable
    {
        return $this->items->getIterator();
    }

    public function count(): int
    {
        return $this->items->count();
    }

    public function hasNext(): bool
    {
        return null !== $this->nextCursor;
    }

    public function hasPrevious(): bool
    {
        return null !== $this->previousCursor;
    }

    public function nextCursor(): ?string
    {
        return $this->nextCursor;
    }

    public function previousCursor(): ?string
    {
        return $this->previousCursor;
    }
}
