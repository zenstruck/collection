<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;

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

        return $qb->result();
    }

    /**
     * @param V $item
     */
    final public function add(object $item, bool $flush = true): static
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
    final public function remove(object $item, bool $flush = true): static
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
        return $this->createQueryBuilder('e')->result()->batch($chunkSize);
    }

    /**
     * @return \Traversable<int,V>
     */
    final public function batchProcess(int $chunkSize = 100): \Traversable
    {
        return $this->createQueryBuilder('e')->result()->batchProcess($chunkSize);
    }

    final public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    final public function getIterator(): \Traversable
    {
        return $this->createQueryBuilder('e')->result();
    }

    /**
     * @param string $alias
     * @param string $indexBy
     *
     * @return ResultQueryBuilder<V>
     */
    public function createQueryBuilder($alias, $indexBy = null): ResultQueryBuilder
    {
        return (new ResultQueryBuilder($this->_em))
            ->select($alias)
            ->from($this->_entityName, $alias, $indexBy)
        ;
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
