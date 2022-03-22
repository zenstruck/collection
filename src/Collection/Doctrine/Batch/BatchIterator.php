<?php

namespace Zenstruck\Collection\Doctrine\Batch;

use Doctrine\Persistence\ObjectManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements \IteratorAggregate<int,V>
 */
class BatchIterator implements \IteratorAggregate
{
    /** @var iterable<int,V> */
    protected iterable $items;
    private ObjectManager $om;
    private int $chunkSize;

    /**
     * @param iterable<int,V> $items
     */
    private function __construct(iterable $items, ObjectManager $om, int $chunkSize = 100)
    {
        $this->items = $items;
        $this->om = $om;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @param iterable<int,V> $items
     *
     * @return self<V>|CountableBatchIterator<V>
     */
    final public static function for(iterable $items, ObjectManager $om, int $chunkSize = 100): self|CountableBatchIterator
    {
        if (\is_countable($items)) {
            return new CountableBatchIterator($items, $om, $chunkSize);
        }

        return new self($items, $om, $chunkSize);
    }

    final public function getIterator(): \Traversable
    {
        $iteration = 0;

        foreach ($this->items as $key => $value) {
            yield $key => $value;

            if (++$iteration % $this->chunkSize) {
                continue;
            }

            $this->om->clear();
        }

        $this->om->clear();
    }
}
