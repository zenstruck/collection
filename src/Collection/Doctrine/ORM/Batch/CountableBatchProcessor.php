<?php

namespace Zenstruck\Collection\Doctrine\ORM\Batch;

/**
 * @property \Countable $items
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CountableBatchProcessor extends BatchProcessor implements \Countable
{
    public function count(): int
    {
        return \count($this->items);
    }
}
