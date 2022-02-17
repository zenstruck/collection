<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements \IteratorAggregate<int,V>
 */
abstract class Repository implements \IteratorAggregate, \Countable
{
    /** @var EntityRepository<V>|null */
    private ?EntityRepository $repo = null;

    final public function getIterator(): \Traversable
    {
        return static::createResult($this->qb());
    }

    /**
     * @return \Traversable<int,V>
     */
    final public function batch(int $chunkSize = 100): \Traversable
    {
        return static::createResult($this->qb())->batch($chunkSize);
    }

    /**
     * @return \Traversable<int,V>
     */
    final public function batchProcess(int $chunkSize = 100): \Traversable
    {
        return static::createResult($this->qb())->batchProcess($chunkSize);
    }

    final public function count(): int
    {
        return $this->repo()->count([]);
    }

    abstract public function getClassName(): string;

    /**
     * @return Result<V>
     */
    protected static function createResult(QueryBuilder $qb, bool $fetchCollection = true, ?bool $useOutputWalkers = null): Result
    {
        return new Result($qb, $fetchCollection, $useOutputWalkers);
    }

    /**
     * @return EntityRepository<V>
     */
    protected function repo(): EntityRepository
    {
        return $this->repo ??= new EntityRepository($this->em(), $this->em()->getClassMetadata($this->getClassName()));
    }

    final protected function qb(string $alias = 'entity', ?string $indexBy = null): QueryBuilder
    {
        return $this->repo()->createQueryBuilder($alias, $indexBy);
    }

    abstract protected function em(): EntityManagerInterface;
}
