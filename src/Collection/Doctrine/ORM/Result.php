<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zenstruck\Collection;
use Zenstruck\Collection\CallbackCollection;
use Zenstruck\Collection\Doctrine\ORM\Batch\BatchIterator;
use Zenstruck\Collection\Doctrine\ORM\Batch\BatchProcessor;
use Zenstruck\Collection\Doctrine\ORM\Batch\IterableResultDecorator;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Result implements Collection
{
    use Paginatable;

    private Query $query;
    private bool $fetchCollection;
    private ?bool $useOutputWalkers;
    private ?int $count = null;

    final public function __construct(Query|QueryBuilder $query, bool $fetchCollection = true, ?bool $useOutputWalkers = null)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        $this->query = $query;
        $this->fetchCollection = $fetchCollection;
        $this->useOutputWalkers = $useOutputWalkers;
    }

    final public function take(int $limit, int $offset = 0): Collection
    {
        return new IterableCollection(
            fn() => \iterator_to_array($this->paginatorFor($this->cloneQuery()->setFirstResult($offset)->setMaxResults($limit)))
        );
    }

    /**
     * By default, iterating detaches objects from the entity manager as they are iterated
     * to conserve memory. To change this behaviour, override this method and return
     * "$this->rawIterator()".
     */
    public function getIterator(): \Traversable
    {
        return $this->batch();
    }

    final public function batch(int $chunkSize = 100): \Traversable
    {
        return BatchIterator::for($this->callbackCollection(), $this->em(), $chunkSize);
    }

    final public function batchProcess(int $chunkSize = 100): \Traversable
    {
        return BatchProcessor::for($this->callbackCollection(), $this->em(), $chunkSize);
    }

    final public function count(): int
    {
        return $this->count ??= $this->paginatorFor($this->cloneQuery())->count();
    }

    final protected function em(): EntityManagerInterface
    {
        return $this->query->getEntityManager();
    }

    final protected function resetCount(): void
    {
        $this->count = null;
    }

    final protected function rawIterator(): iterable
    {
        $query = $this->cloneQuery();

        if (!\method_exists($query, 'toIterable')) {
            // AbstractQuery::toIterable() was introduced in ORM 2.8 and fixes
            // the issue IterableResultDecorator solves so it is no longer required.
            return new IterableResultDecorator($this->cloneQuery()->iterate());
        }

        return $query->toIterable();
    }

    private function callbackCollection(): CallbackCollection
    {
        return new CallbackCollection(fn() => $this->rawIterator(), [$this, 'count']);
    }

    private function paginatorFor(Query $query): Paginator
    {
        return (new Paginator($query, $this->fetchCollection))->setUseOutputWalkers($this->useOutputWalkers);
    }

    private function cloneQuery(): Query
    {
        $query = clone $this->query;
        $query->setParameters($this->query->getParameters());

        foreach ($this->query->getHints() as $name => $value) {
            $query->setHint($name, $value);
        }

        return $query;
    }
}
