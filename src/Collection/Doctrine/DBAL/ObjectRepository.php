<?php

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ObjectRepository extends Repository
{
    protected static function createResult(QueryBuilder $qb): ObjectResult
    {
        return new ObjectResult(fn(array $data) => static::createObject($data), $qb, static::countModifier());
    }

    abstract protected static function createObject(array $data): object;
}
