<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection;
use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Repository as RepositoryContract;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @implements RepositoryContract<int,V>
 */
abstract class Repository implements RepositoryContract
{
    /** @var EntityRepository<V>|null */
    private ?EntityRepository $repo = null;

    public function get(mixed $id): object
    {
        if (!$object = $this->em()->find($this->getClassName(), $id)) {
            throw $this->createNotFoundException($id);
        }

        /** @var V $object */
        return $object;
    }

    /**
     * @param array<string,mixed> $criteria
     *
     * @return Collection<int,V>
     */
    public function filter(mixed $criteria): Collection
    {
        if (!\is_array($criteria) || array_is_list($criteria)) {
            throw new \InvalidArgumentException('$context must be non-list array.');
        }

        return new IterableCollection($this->repo()->findBy($criteria));
    }

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

    /**
     * @return class-string
     */
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

    /**
     * Override to customize the not found exception.
     */
    protected function createNotFoundException(mixed $id): \RuntimeException
    {
        return new \RuntimeException(\sprintf('"%s" with id "%s" not found.',
            $this->getClassName(),
            \is_scalar($id) ? $id : \sprintf('(%s)', \get_debug_type($id))
        ));
    }

    abstract protected function em(): EntityManagerInterface;
}
