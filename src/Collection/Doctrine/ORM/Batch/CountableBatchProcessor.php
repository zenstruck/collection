<?php

namespace Zenstruck\Collection\Doctrine\ORM\Batch;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @extends BatchProcessor<Value>
 */
final class CountableBatchProcessor extends BatchProcessor implements \Countable
{
    public function count(): int
    {
        return \is_countable($this->items) ? \count($this->items) : throw new \LogicException('Not countable.');
    }
}
