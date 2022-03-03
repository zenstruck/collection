<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zenstruck\Collection;
use Zenstruck\Collection\CallbackCollection;
use Zenstruck\Collection\Doctrine\ORM\Batch\BatchIterator;
use Zenstruck\Collection\Doctrine\ORM\Batch\BatchProcessor;
use Zenstruck\Collection\FactoryCollection;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements Collection<int,V>
 */
final class Result implements Collection
{
    /** @use Paginatable<V> */
    use Paginatable;

    private Query $query;
    private bool $fetchCollection;
    private ?bool $useOutputWalkers;
    private ?int $count = null;
    private bool $hasAggregates = false;

    public function __construct(Query|QueryBuilder $query, bool $fetchCollection = true, ?bool $useOutputWalkers = null)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        $this->query = $query;
        $this->fetchCollection = $fetchCollection;
        $this->useOutputWalkers = $useOutputWalkers;
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        $collection = new IterableCollection(
            fn() => \iterator_to_array($this->paginatorFor($this->cloneQuery()->setFirstResult($offset)->setMaxResults($limit)))
        );

        if (!$this->hasAggregates) {
            return $collection;
        }

        return new FactoryCollection($collection, function(mixed $result): EntityWithAggregates {
            if (!\is_array($result) || !isset($result[0]) || !\is_object($result[0])) {
                throw new \LogicException(\sprintf('Results does not contain aggregate fields, do not call %s::withAggregates().', self::class));
            }

            $entity = $result[0];

            unset($result[0]);

            return new EntityWithAggregates($entity, $result);
        });
    }

    /**
     * By default, iterating detaches objects from the entity manager as they are iterated
     * to conserve memory. To change this behaviour, override this method and return
     * {@see rawIterator()}.
     */
    public function getIterator(): \Traversable
    {
        return $this->batch();
    }

    /**
     * @return \Traversable<int,V>
     */
    public function batch(int $chunkSize = 100): \Traversable
    {
        return BatchIterator::for($this->callbackCollection(), $this->em(), $chunkSize);
    }

    /**
     * @return \Traversable<int,V>
     */
    public function batchProcess(int $chunkSize = 100): \Traversable
    {
        return BatchProcessor::for($this->callbackCollection(), $this->em(), $chunkSize);
    }

    /**
     * Delete the current result set from the database.
     *
     * @param null|callable(object):void $callback
     */
    public function delete(?callable $callback = null): int
    {
        $count = 0;

        foreach ($this->batchProcess() as $entity) {
            $entity = $entity instanceof EntityWithAggregates ? $entity->entity() : $entity;

            try {
                $this->em()->remove($entity);
            } catch (ORMInvalidArgumentException|MappingException $e) {
                throw new \LogicException('Can only delete results of the managed object.', 0, $e);
            }

            if ($callback) {
                $callback($entity);
            }

            ++$count;
        }

        // reset the cached count for the result set
        $this->count = null;

        return $count;
    }

    public function count(): int
    {
        return $this->count ??= $this->paginatorFor($this->cloneQuery())->count();
    }

    /**
     * Call this before iterating/paginating if your query result
     * contains "aggregate fields" (extra columns not associated
     * with your entity). This wraps each entity in a
     * {@see EntityWithAggregates} object.
     *
     * When iterating over large sets, there is a slight performance
     * impact. Doctrine does not allow iterating over aggregate
     * results directly chunk the results into groups of 20. Each
     * chunk requires additional queries.
     *
     * @return self<EntityWithAggregates<V>>
     */
    public function withAggregates(): self
    {
        $this->hasAggregates = true;

        return $this;
    }

    private function em(): EntityManagerInterface
    {
        return $this->query->getEntityManager();
    }

    /**
     * @return iterable<mixed>
     */
    private function rawIterator(): iterable
    {
        if (!$this->hasAggregates) {
            try {
                yield from $this->cloneQuery()->toIterable();
            } catch (QueryException $e) {
                if ($e->getMessage() === QueryException::iterateWithMixedResultNotAllowed()->getMessage()) {
                    throw new \LogicException(\sprintf('Results contain aggregate fields, call %s::withAggregates().', self::class), 0, $e);
                }

                throw $e;
            }

            return;
        }

        foreach ($this->pages(20) as $page) {
            yield from $page;
        }
    }

    /**
     * @return CallbackCollection<int,mixed>
     */
    private function callbackCollection(): CallbackCollection
    {
        return new CallbackCollection(fn() => $this->rawIterator(), [$this, 'count']);
    }

    /**
     * @return Paginator<V>
     */
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
