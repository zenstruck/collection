<?php

namespace Zenstruck\Collection\Doctrine\ORM\Batch;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @extends BatchIterator<Value>
 */
final class CountableBatchIterator extends BatchIterator implements \Countable
{
    public function count(): int
    {
        return \is_countable($this->items) ? \count($this->items) : throw new \LogicException('Not countable.');
    }
}
