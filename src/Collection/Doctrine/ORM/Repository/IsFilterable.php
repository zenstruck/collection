<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Doctrine\ORM\Specification\ORMContext;
use Zenstruck\Collection\Filterable;
use Zenstruck\Collection\Specification\Interpreter;
use Zenstruck\Collection\Specification\SpecificationInterpreter;

/**
 * Enables your repository to implement {@see Filterable}.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 */
trait IsFilterable
{
    /**
     * @param mixed|array<string,mixed> $specification
     *
     * @return Result<V>
     */
    final public function filter(mixed $specification): Result
    {
        if (!$this instanceof EntityRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, EntityRepository::class));
        }

        if (\is_array($specification) && !array_is_list($specification)) {
            // using standard "criteria"
            return parent::filter($specification);
        }

        return static::resultFor($this->qbForSpecification($specification));
    }

    /**
     * @return V
     */
    final public function get(mixed $specification): object
    {
        if (!$this instanceof EntityRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, EntityRepository::class));
        }

        if (\is_scalar($specification) || (\is_array($specification) && !array_is_list($specification))) {
            // using id
            return parent::get($specification);
        }

        if (!\is_object($result = $this->qbForSpecification($specification)->getQuery()->getOneOrNullResult())) {
            throw $this->createNotFoundException($specification);
        }

        /** @var V $result */
        return $result;
    }

    protected function qbForSpecification(mixed $specification): QueryBuilder
    {
        $result = $this->specificationInterpreter()->interpret(
            $specification,
            new ORMContext($qb = $this->createQueryBuilder('entity'), 'entity')
        );

        if ($result) {
            $qb->where($result);
        }

        return $qb;
    }

    protected function createNotFoundException(mixed $specification): \RuntimeException
    {
        if (\is_scalar($specification) || (\is_array($specification) && !array_is_list($specification))) {
            return parent::createNotFoundException($specification);
        }

        return new \RuntimeException(\sprintf('Object "%s" not found for specification "%s".', $this->getClassName(), SpecificationInterpreter::stringify($specification)));
    }

    /**
     * Override to provide your own Specification Interpreter implementation.
     */
    protected function specificationInterpreter(): Interpreter
    {
        return ORMContext::defaultInterpreter();
    }
}
