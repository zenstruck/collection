<?php

namespace Zenstruck\Collection\Doctrine\ORM\Batch;

/**
 * @property \Countable $items
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CountableBatchIterator extends BatchIterator implements \Countable
{
    public function count(): int
    {
        return \count($this->items);
    }
}
