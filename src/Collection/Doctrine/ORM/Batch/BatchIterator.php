<?php

namespace Zenstruck\Collection\Doctrine\ORM\Batch;

use Doctrine\ORM\EntityManagerInterface;

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
    private EntityManagerInterface $em;
    private int $chunkSize;

    /**
     * @param iterable<int,V> $items
     */
    private function __construct(iterable $items, EntityManagerInterface $em, int $chunkSize = 100)
    {
        $this->items = $items;
        $this->em = $em;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @param iterable<int,V> $items
     *
     * @return self<V>|CountableBatchIterator<V>
     */
    final public static function for(iterable $items, EntityManagerInterface $em, int $chunkSize = 100): self|CountableBatchIterator
    {
        if (\is_countable($items)) {
            return new CountableBatchIterator($items, $em, $chunkSize);
        }

        return new self($items, $em, $chunkSize);
    }

    final public function getIterator(): \Traversable
    {
        $iteration = 0;

        foreach ($this->items as $key => $value) {
            yield $key => $value;

            if (++$iteration % $this->chunkSize) {
                continue;
            }

            $this->em->clear();
        }

        $this->em->clear();
    }
}
