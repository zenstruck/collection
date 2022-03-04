<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @extends BaseEntityRepository<V>
 * @implements \IteratorAggregate<int,V>
 */
class EntityRepository extends BaseEntityRepository implements \IteratorAggregate, \Countable
{
    /**
     * @return V
     *
     * @throws \RuntimeException if not found
     */
    public function get(mixed $id): object
    {
        if (!$object = $this->find($id)) {
            throw $this->createNotFoundException($id);
        }

        return $object;
    }

    /**
     * @param array<string,mixed> $criteria
     *
     * @return Result<V>
     */
    public function filter(array $criteria): Result
    {
        $qb = $this->createQueryBuilder('e');

        foreach ($criteria as $field => $value) {
            $qb->andWhere("e.{$field} = :{$field}")->setParameter($field, $value);
        }

        return self::createResult($qb);
    }

    /**
     * @param V $item
     */
    final public function add(mixed $item, bool $flush = true): static
    {
        if (!\is_a($item, $this->getClassName())) {
            throw new \InvalidArgumentException(\sprintf('%s::%s() can only be used on entities of type "%s".', static::class, __FUNCTION__, $this->getClassName()));
        }

        $this->_em->persist($item);

        if ($flush) {
            $this->_em->flush();
        }

        return $this;
    }

    /**
     * @param V $item
     */
    final public function remove(mixed $item, bool $flush = true): static
    {
        if (!\is_a($item, $this->getClassName())) {
            throw new \InvalidArgumentException(\sprintf('%s::%s() can only be used on entities of type "%s".', static::class, __FUNCTION__, $this->getClassName()));
        }

        $this->_em->remove($item);

        if ($flush) {
            $this->_em->flush();
        }

        return $this;
    }

    final public function flush(): static
    {
        $this->_em->flush();

        return $this;
    }

    /**
     * @return \Traversable<int,V>
     */
    final public function batch(int $chunkSize = 100): \Traversable
    {
        return static::createResult($this->createQueryBuilder('e'))->batch($chunkSize);
    }

    /**
     * @return \Traversable<int,V>
     */
    final public function batchProcess(int $chunkSize = 100): \Traversable
    {
        return static::createResult($this->createQueryBuilder('e'))->batchProcess($chunkSize);
    }

    final public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    final public function getIterator(): \Traversable
    {
        return static::createResult($this->createQueryBuilder('e'));
    }

    /**
     * @return Result<V>
     */
    final protected static function createResult(QueryBuilder $qb): Result
    {
        return new Result($qb);
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
}
