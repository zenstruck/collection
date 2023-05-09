<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\QueryException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Zenstruck\Collection;
use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\Doctrine\Batch;
use Zenstruck\Collection\Doctrine\Result;
use Zenstruck\Collection\FactoryCollection;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\LazyCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements Result<V>
 */
final class EntityResult implements Result
{
    /** @use IterableCollection<int,V> */
    use IterableCollection;

    private const ENTITY_WITH_AGGREGATES = [EntityWithAggregates::class, 'create'];

    /** @var callable(mixed):mixed */
    private $resultModifier;

    /** @var Query::HYDRATE_*|null */
    private ?int $hydrationMode = null;

    private bool $readonly = false;
    private int $count;

    public function __construct(private QueryBuilder $qb)
    {
    }

    public function batchIterate(int $chunkSize = 100): \Traversable
    {
        return Batch::iterate($this, $this->em(), $chunkSize);
    }

    public function batchProcess(int $chunkSize = 100): \Traversable
    {
        return Batch::process($this, $this->em(), $chunkSize);
    }

    /**
     * @return self<V>
     */
    public function readonly(): self
    {
        $clone = clone $this;
        $clone->readonly = true;

        return $clone;
    }

    public function first(mixed $default = null): mixed
    {
        try {
            return $this->normalizeResult($this->query()->setMaxResults(1)->getSingleResult());
        } catch (NoResultException) {
            return $default;
        }
    }

    /**
     * @return self<scalar>
     */
    public function asScalar(?string $field = null): self
    {
        $clone = clone $this;
        $clone->hydrationMode = Query::HYDRATE_SCALAR_COLUMN;

        if ($field) {
            $clone->qb = clone $this->qb;
            $clone->qb->select(\sprintf('%s.%s', $clone->qb->getRootAliases()[0], $field));
        }

        return $clone;
    }

    /**
     * @return self<string>
     */
    public function asString(?string $field = null): self
    {
        return $this->asScalar($field)->as(static fn(bool|string|int|float $row) => (string) $row);
    }

    /**
     * @return self<int>
     */
    public function asInt(?string $field = null): self
    {
        return $this->asScalar($field)->as(static fn(bool|string|int|float $row) => (int) $row);
    }

    /**
     * @return self<float>
     */
    public function asFloat(?string $field = null): self
    {
        return $this->asScalar($field)->as(static fn(bool|string|int|float $row) => (float) $row);
    }

    /**
     * @return self<array<string,mixed>>
     */
    public function asArray(string ...$fields): self
    {
        $clone = clone $this;
        $clone->hydrationMode = Query::HYDRATE_ARRAY;

        if ($fields) {
            $root = $this->qb->getRootAliases()[0];
            $clone->qb = clone $this->qb;
            $clone->qb->select(\array_map(fn($f) => \sprintf('%s.%s', $root, $f), $fields));
        }

        return $clone;
    }

    /**
     * @template R
     *
     * @param callable(mixed):R $modifier
     *
     * @return self<R>
     */
    public function as(callable $modifier): self
    {
        $clone = clone $this;
        $clone->resultModifier = $modifier;

        return $clone;
    }

    /**
     * Call this before iterating/paginating if your query result
     * contains "aggregate fields" (extra columns not associated
     * with your entity). This wraps each entity in an
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
        return $this->as(self::ENTITY_WITH_AGGREGATES); // @phpstan-ignore-line
    }

    public function take(int $limit, int $offset = 0): Collection
    {
        return new FactoryCollection(
            new LazyCollection(
                fn() => \iterator_to_array($this->paginator($this->query()->setFirstResult($offset)->setMaxResults($limit))),
            ),
            fn(mixed $result): mixed => $this->normalizeResult($result),
        );
    }

    public function getIterator(): \Traversable
    {
        if (self::ENTITY_WITH_AGGREGATES === $this->resultModifier || Query::HYDRATE_SCALAR_COLUMN === $this->hydrationMode) {
            foreach ($this->pages(20) as $page) {
                yield from $page;
            }

            return;
        }

        try {
            yield from $this->query()->toIterable(hydrationMode: $this->hydrationMode ?? Query::HYDRATE_OBJECT);
        } catch (QueryException $e) {
            if ($e->getMessage() === QueryException::iterateWithMixedResultNotAllowed()->getMessage()) {
                throw new \LogicException(\sprintf('Results contain aggregate fields, call %s::withAggregates().', self::class), 0, $e);
            }

            throw $e;
        }
    }

    public function eager(): ArrayCollection
    {
        return new ArrayCollection(\array_map([$this, 'normalizeResult'], $this->query()->execute()));
    }

    public function count(): int
    {
        return $this->count ??= $this->paginator()->count();
    }

    private function em(): EntityManagerInterface
    {
        return $this->qb->getEntityManager();
    }

    private function query(): Query
    {
        $query = $this->qb->getQuery();

        if ($this->hydrationMode) {
            $query->setHydrationMode($this->hydrationMode);
        }

        return $query;
    }

    private function normalizeResult(mixed $result): mixed
    {
        if ($this->resultModifier) {
            $result = ($this->resultModifier)($result);
        }

        if ($this->readonly && \is_object($result)) {
            $this->em()->detach($result instanceof EntityWithAggregates ? $result->entity() : $result);
        }

        return $result;
    }

    /**
     * @return Paginator<V>
     */
    private function paginator(?Query $query = null): Paginator
    {
        $paginator = new Paginator($query ?? $this->query());

        if ($this->hydrationMode) {
            $paginator->setUseOutputWalkers(false);
        }

        return $paginator;
    }
}
