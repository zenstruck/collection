<?php

namespace Zenstruck\Collection\Doctrine\Batch;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends BatchIterator<V>
 */
final class CountableBatchIterator extends BatchIterator implements \Countable
{
    public function count(): int
    {
        return \is_countable($this->items) ? \count($this->items) : throw new \LogicException('Not countable.');
    }
}
