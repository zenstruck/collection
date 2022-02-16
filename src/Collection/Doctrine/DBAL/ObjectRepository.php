<?php

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @extends Repository<Value>
 */
abstract class ObjectRepository extends Repository
{
    /**
     * @return ObjectResult<Value>
     */
    protected static function createResult(QueryBuilder $qb): ObjectResult
    {
        return new ObjectResult(fn(array $data) => static::createObject($data), $qb, static::countModifier());
    }

    /**
     * @param mixed[] $data
     */
    abstract protected static function createObject(array $data): object;
}
