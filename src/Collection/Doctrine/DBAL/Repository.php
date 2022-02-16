<?php

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 * @implements \IteratorAggregate<int,Value>
 */
abstract class Repository implements \IteratorAggregate, \Countable
{
    public function getIterator(): \Traversable
    {
        return static::createResult($this->qb());
    }

    public function count(): int
    {
        return static::createResult($this->qb())->count();
    }

    /**
     * @return Result<Value>
     */
    protected static function createResult(QueryBuilder $qb): Result
    {
        return new Result($qb, static::countModifier());
    }

    final protected function qb(?string $alias = null): QueryBuilder
    {
        return $this->connection()->createQueryBuilder()->select('*')->from(static::tableName(), $alias);
    }

    /**
     * Override to define your own count modifier.
     */
    protected static function countModifier(): ?callable
    {
        return null;
    }

    abstract protected static function tableName(): string;

    abstract protected function connection(): Connection;
}
