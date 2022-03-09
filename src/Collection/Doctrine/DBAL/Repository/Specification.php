<?php

namespace Zenstruck\Collection\Doctrine\DBAL\Repository;

use Doctrine\DBAL\Query\QueryBuilder;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository;
use Zenstruck\Collection\Doctrine\DBAL\Result;
use Zenstruck\Collection\Doctrine\DBAL\Specification\DBALContext;
use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\SpecificationInterpreter;

/**
 * Enables your repository to use the specification system.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
trait Specification
{
    /**
     * @return Result<V>
     */
    final public function filter(mixed $specification): Result
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, Repository::class));
        }

        return static::createResult($this->qbForSpecification($specification));
    }

    /**
     * @return V
     */
    final public function get(mixed $specification): mixed
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, Repository::class));
        }

        $result = $this->qbForSpecification($specification)
            ->setMaxResults(1)
            ->{\method_exists(QueryBuilder::class, 'executeQuery') ? 'executeQuery' : 'execute'}()
            ->fetchAssociative()
        ;

        if (!$result) {
            throw $this->createNotFoundException($specification);
        }

        return $this instanceof ObjectRepository ? static::createObject($result) : $result;
    }

    final protected function qbForSpecification(mixed $specification): QueryBuilder
    {
        $qb = $this->qb('entity');
        $result = $this->specificationInterpreter()->interpret($specification, new DBALContext($qb, 'entity'));

        if ($result) {
            $qb->where($result);
        }

        return $qb;
    }

    /**
     * Override to provide your own implementation.
     */
    protected function createNotFoundException(mixed $specification): \RuntimeException
    {
        throw new \RuntimeException(\sprintf('Data from "%s" table not found for specification "%s".', static::tableName(), SpecificationInterpreter::stringify($specification)));
    }

    /**
     * Override to provide your own Specification Interpreter implementation.
     */
    protected function specificationInterpreter(): Interpreter
    {
        return DBALContext::defaultInterpreter();
    }
}
