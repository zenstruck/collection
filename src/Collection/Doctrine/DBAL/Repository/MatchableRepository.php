<?php

namespace Zenstruck\Collection\Doctrine\DBAL\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository;
use Zenstruck\Collection\Doctrine\DBAL\Result;
use Zenstruck\Collection\Doctrine\DBAL\Specification\DBALContext;
use Zenstruck\Collection\Exception\NotFound;
use Zenstruck\Collection\Specification\Normalizer;

/**
 * Enables your repository to implement Zenstruck\Collection\Matchable.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait MatchableRepository
{
    final public function match($specification): Result
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        return static::createResult($this->qbForSpecification($specification));
    }

    final public function matchOne($specification)
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        $result = $this->qbForSpecification($specification)
            ->setMaxResults(1)
            ->execute()
            ->fetch()
        ;

        if (!$result) {
            throw new NotFound(\sprintf('Data from "%s" table not found for given specification.', static::tableName()));
        }

        return $this instanceof ObjectRepository ? static::createObject($result) : $result;
    }

    final protected function qbForSpecification($specification): QueryBuilder
    {
        $qb = $this->qb('entity');
        $result = $this->specificationNormalizer()->normalize($specification, new DBALContext($qb, 'entity'));

        if ($result) {
            $qb->where($result);
        }

        return $qb;
    }

    /**
     * Override to provide your own SpecificationNormalizer implementation.
     */
    protected function specificationNormalizer(): Normalizer
    {
        return DBALContext::defaultNormalizer();
    }
}
