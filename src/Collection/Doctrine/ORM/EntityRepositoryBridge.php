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
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 */
trait EntityRepositoryBridge
{
    /** @var EntityRepository<V> */
    private EntityRepository $collectionRepo;

    /**
     * @param mixed|Criteria|array<string,mixed>|(object&callable(QueryBuilder,string):void)|object $specification
     * @param LockMode|int|null                                                                     $lockMode
     * @param int|null                                                                              $lockVersion
     */
    public function find($specification, $lockMode = null, $lockVersion = null): ?object
    {
        if ($lockMode || $lockVersion) {
            // @phpstan-ignore-next-line
            return $this->getEntityManager()->find($this->getEntityName(), $specification, $lockMode, $lockVersion);
        }

        return $this->collectionRepo()->find($specification);
    }

    /**
     * @param Criteria|null|array<string,mixed>|(object&callable(QueryBuilder,string):void)|object $specification
     *
     * @return EntityResult<V>
     */
    public function query(mixed $specification): EntityResult
    {
        return $this->collectionRepo()->query($specification);
    }

    /**
     * @param Criteria|null|array<string,mixed>|(object&callable(QueryBuilder,string):void)|object $specification
     *
     * @return EntityResult<V>
     */
    public function filter(mixed $specification): EntityResult
    {
        return $this->query($specification);
    }

    public function getIterator(): \Traversable
    {
        return $this->collectionRepo()->getIterator();
    }

    public function count(array $criteria = []): int
    {
        return parent::count($criteria);
    }

    /**
     * @param string      $alias
     * @param string|null $indexBy
     *
     * @return EntityResultQueryBuilder<V>
     */
    public function createQueryBuilder($alias, $indexBy = null): EntityResultQueryBuilder
    {
        return EntityResultQueryBuilder::forEntity(
            parent::createQueryBuilder($alias, $indexBy)->getEntityManager(),
            $this->getClassName(),
            $alias,
            $indexBy
        );
    }

    /**
     * @return EntityResultQueryBuilder<V>
     */
    protected function qb(string $alias = 'e', ?string $indexBy = null): EntityResultQueryBuilder
    {
        return $this->createQueryBuilder($alias, $indexBy);
    }

    /**
     * @return EntityRepository<V>
     */
    private function collectionRepo(): EntityRepository
    {
        return $this->collectionRepo ??= new EntityRepository($this->getEntityManager(), $this->getEntityName());
    }
}
