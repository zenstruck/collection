<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ObjectRepository as PersistenceObjectRepository;
use Zenstruck\Collection\Repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @implements PersistenceObjectRepository<V>
 * @implements Repository<int,V>
 */
abstract class ObjectRepository implements PersistenceObjectRepository, Repository
{
    /** @var EntityRepository<V>|null */
    private ?EntityRepository $repo = null;

    public function get(mixed $id): object
    {
        if (!$object = $this->find($id)) {
            throw $this->createNotFoundException($id);
        }

        /** @var V $object */
        return $object;
    }

    /**
     * @param array<string,mixed> $criteria
     *
     * @return Result<V>
     */
    public function filter(mixed $criteria): Result
    {
        if (!\is_array($criteria) || array_is_list($criteria)) {
            throw new \InvalidArgumentException('$context must be non-list array.');
        }

        $qb = $this->repo()->createQueryBuilder('e');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.{$field} = :{$field}")->setParameter($field, $value);
        }

        return self::createResult($qb);
    }

    /**
     * @see EntityRepository::find()
     *
     * @param LockMode::*|null $lockMode
     *
     * @return ?V
     */
    final public function find($id, ?int $lockMode = null, ?int $lockVersion = null): ?object
    {
        return $this->repo()->find($id, $lockMode, $lockVersion);
    }

    /**
     * @see EntityRepository::findAll()
     */
    final public function findAll(): array
    {
        return $this->repo()->findAll();
    }

    /**
     * @see EntityRepository::findBy()
     */
    final public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null): array
    {
        return $this->repo()->findBy($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @see EntityRepository::findOneBy()
     *
     * @param array<string,string>|null $orderBy
     *
     * @return ?V
     */
    final public function findOneBy(array $criteria, ?array $orderBy = null): ?object
    {
        return $this->repo()->findOneBy($criteria, $orderBy);
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
