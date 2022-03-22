<?php

namespace Zenstruck\Collection\Doctrine\Batch;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * @author Marco Pivetta <ocramius@gmail.com>
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements \IteratorAggregate<int,V>
 */
class BatchProcessor implements \IteratorAggregate
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
     * @return self<V>|CountableBatchProcessor<V>
     */
    final public static function for(iterable $items, ObjectManager $om, int $chunkSize = 100): self|CountableBatchProcessor
    {
        if (\is_countable($items)) {
            return new CountableBatchProcessor($items, $om, $chunkSize);
        }

        return new self($items, $om, $chunkSize);
    }

    final public function getIterator(): \Traversable
    {
        if ($this->om instanceof EntityManagerInterface) {
            $logger = $this->om->getConfiguration()->getSQLLogger();
            $this->om->getConfiguration()->setSQLLogger(null);
            $this->om->beginTransaction();
        }

        $iteration = 0;

        try {
            foreach ($this->items as $key => $value) {
                yield $key => $value;

                $this->flushAndClearBatch(++$iteration);
            }
        } catch (\Throwable $e) {
            if ($this->om instanceof EntityManagerInterface) {
                $this->om->rollback();
            }

            throw $e;
        }

        $this->flushAndClear();

        if ($this->om instanceof EntityManagerInterface) {
            $this->om->commit();
            $this->om->getConfiguration()->setSQLLogger($logger);
        }
    }

    private function flushAndClearBatch(int $iteration): void
    {
        if ($iteration % $this->chunkSize) {
            return;
        }

        $this->flushAndClear();
    }

    private function flushAndClear(): void
    {
        $this->om->flush();
        $this->om->clear();
    }
}
