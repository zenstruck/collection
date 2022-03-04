<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMInvalidArgumentException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zenstruck\Collection;
use Zenstruck\Collection\ArrayCollection;
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
    private ?bool $useOutputWalkers = null;
    private bool $fetchJoinCollection = true;
    private ?int $count = null;
    private ?\Closure $resultNormalizer = null;

    public function __construct(Query|QueryBuilder $query)
    {
        if ($query instanceof QueryBuilder) {
            $query = $query->getQuery();
        }

        $this->query = $query;
    }

    /**
     * @return self<V>
     */
    public static function for(Query|QueryBuilder $query): self
    {
        return new self($query);
    }

    /**
     * @template D
     *
     * @param D $default
     *
     * @return V|D
     */
    public function first(mixed $default = null): mixed
    {
        try {
            return $this->normalizeResult($this->cloneQuery()->setMaxResults(1)->getSingleResult());
        } catch (NoResultException) {
            return $default;
        }
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        $collection = new IterableCollection(
            fn() => \iterator_to_array($this->paginatorFor($this->cloneQuery()->setFirstResult($offset)->setMaxResults($limit)))
        );

        if (!$this->resultNormalizer) {
            return $collection;
        }

        return new FactoryCollection($collection, function(mixed $result): mixed {
            return $this->normalizeResult($result);
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
     * @see Paginator::__construct()
     *
     * @return $this
     */
    public function disableFetchJoinCollection(): self
    {
        $this->fetchJoinCollection = false;

        return $this;
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
     * @return self<EntityWithAggregates<V&object>>
     */
    public function withAggregates(): self
    {
        $this->resultNormalizer = static function(mixed $result): EntityWithAggregates {
            if (!\is_array($result) || !isset($result[0]) || !\is_object($result[0])) {
                throw new \LogicException(\sprintf('Results does not contain aggregate fields, do not call %s::withAggregates().', self::class));
            }

            $entity = $result[0];

            unset($result[0]);

            return new EntityWithAggregates($entity, $result);
        };

        return $this;
    }

    /**
     * @return self<scalar>
     */
    public function asScalar(): self
    {
        $this->query->setHydrationMode(Query::HYDRATE_SCALAR_COLUMN);
        $this->useOutputWalkers = false;

        return $this;
    }

    /**
     * @return self<int>
     */
    public function asInt(): self
    {
        $this->asScalar();
        $this->resultNormalizer = static function(mixed $value): int {
            if (!\is_numeric($value)) {
                throw new \LogicException(\sprintf('Expected result(s) to be "int" but got "%s".', \get_debug_type($value)));
            }

            return (int) $value;
        };

        return $this;
    }

    /**
     * @return self<float>
     */
    public function asFloat(): self
    {
        $this->asScalar();
        $this->resultNormalizer = static function(mixed $value): float {
            if (!\is_numeric($value)) {
                throw new \LogicException(\sprintf('Expected result(s) to be "float" but got "%s".', \get_debug_type($value)));
            }

            return (float) $value;
        };

        return $this;
    }

    /**
     * @return self<string>
     */
    public function asString(): self
    {
        $this->asScalar();
        $this->resultNormalizer = static fn(mixed $value): string => (string) $value;

        return $this;
    }

    /**
     * @return self<mixed[]>
     */
    public function asArray(): self
    {
        $this->query->setHydrationMode(Query::HYDRATE_ARRAY);
        $this->useOutputWalkers = false;

        return $this;
    }

    /**
     * @return array<int,V>
     */
    public function toArray(): array
    {
        if ($this->resultNormalizer) {
            return \array_map([$this, 'normalizeResult'], $this->query->execute());
        }

        return $this->query->execute();
    }

    /**
     * @return ArrayCollection<int,V>
     */
    public function eager(): ArrayCollection
    {
        return new ArrayCollection($this->toArray());
    }

    private function normalizeResult(mixed $result): mixed
    {
        if (!$this->resultNormalizer) {
            return $result;
        }

        return ($this->resultNormalizer)($result);
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
        if ($this->resultNormalizer || Query::HYDRATE_SCALAR_COLUMN === $this->query->getHydrationMode()) {
            foreach ($this->pages(20) as $page) {
                yield from $page;
            }

            return;
        }

        try {
            yield from $this->cloneQuery()->toIterable([], $this->query->getHydrationMode());
        } catch (QueryException $e) {
            if ($e->getMessage() === QueryException::iterateWithMixedResultNotAllowed()->getMessage()) {
                throw new \LogicException(\sprintf('Results contain aggregate fields, call %s::withAggregates().', self::class), 0, $e);
            }

            throw $e;
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
        return (new Paginator($query, $this->fetchJoinCollection))->setUseOutputWalkers($this->useOutputWalkers);
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
