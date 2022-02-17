<?php

namespace Zenstruck\Collection\Doctrine\ORM\Batch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Internal\Hydration\IterableResult;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @implements \IteratorAggregate<int,Value>
 */
class BatchIterator implements \IteratorAggregate
{
    /** @var iterable<int,Value> */
    protected iterable $items;
    private EntityManagerInterface $em;
    private int $chunkSize;

    /**
     * @param iterable<int,Value> $items
     */
    private function __construct(iterable $items, EntityManagerInterface $em, int $chunkSize = 100)
    {
        if ($items instanceof IterableResult) {
            $items = new IterableResultDecorator($items);
        }

        $this->items = $items;
        $this->em = $em;
        $this->chunkSize = $chunkSize;
    }

    /**
     * @param iterable<int,Value> $items
     *
     * @return self<Value>|CountableBatchIterator<Value>
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
