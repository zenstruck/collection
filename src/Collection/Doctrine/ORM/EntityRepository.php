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

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection\Doctrine\ObjectRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @implements ObjectRepository<V>
 */
class EntityRepository implements ObjectRepository
{
    /**
     * @param class-string<V> $class
     */
    public function __construct(private EntityManagerInterface $em, private string $class)
    {
    }

    /**
     * @param mixed|Criteria|array<string,mixed>|callable(QueryBuilder):void $specification
     */
    public function find(mixed $specification): ?object
    {
        try {
            if ($specification instanceof Criteria) {
                return $this->qb()->addCriteria($specification)->getQuery()->getSingleResult();
            }

            if (\is_array($specification) && !array_is_list($specification)) {
                return $this->em()->getUnitOfWork()->getEntityPersister($this->class)->load($specification, limit: 1); // @phpstan-ignore-line
            }

            if (\is_callable($specification)) {
                $specification($qb = $this->qb(), 'e');

                return $qb->getQuery()->getSingleResult();
            }

            return $this->em()->find($this->class, $specification);
        } catch (NoResultException) {
            return null;
        }
    }

    /**
     * @param Criteria|array<string,mixed>|callable(QueryBuilder):void $specification
     *
     * @return EntityResult<V>
     */
    public function filter(mixed $specification): EntityResult
    {
        $qb = $this->qb();

        if ($specification instanceof Criteria) {
            return $qb->addCriteria($specification)->result();
        }

        if (\is_callable($specification)) {
            $specification($qb, 'e');

            return $qb->result();
        }

        if (!\is_array($specification)) {
            throw new \InvalidArgumentException(\sprintf('Unsupported specification type "%s" - only array|Criteria|callable(QueryBuilder) supported.', \get_debug_type($specification)));
        }

        foreach ($specification as $field => $value) {
            $qb->andWhere("e.{$field} = :{$field}")->setParameter($field, $value);
        }

        return $qb->result();
    }

    public function count(): int
    {
        return $this->qb()->result()->count();
    }

    public function getIterator(): \Traversable
    {
        return $this->qb()->result()->batchIterate();
    }

    /**
     * @return EntityResultQueryBuilder<V>
     */
    final protected function qb(string $alias = 'e', ?string $indexBy = null): EntityResultQueryBuilder
    {
        return (new EntityResultQueryBuilder($this->em))
            ->select($alias)
            ->from($this->class, $alias, $indexBy)
        ;
    }

    final protected function em(): EntityManagerInterface
    {
        return $this->em;
    }
}
