<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection\Doctrine\ORM\Repository;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Doctrine\ORM\Specification\ORMContext;
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

        if (!$result = $this->qbForSpecification($specification)->getQuery()->getOneOrNullResult()) {
            throw new NotFound("{$this->getClassName()} not found for given specification.");
        }

        return $result;
    }

    protected function qbForSpecification($specification): QueryBuilder
    {
        $result = $this->specificationNormalizer()->normalize(
            $specification,
            new ORMContext($qb = $this->qb('entity'), 'entity')
        );

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
        return ORMContext::defaultNormalizer();
    }
}
