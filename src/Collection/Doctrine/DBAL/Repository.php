<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @implements \IteratorAggregate<int,V>
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
     * @return Result<V>
     */
    protected static function createResult(QueryBuilder $qb): Result
    {
        return new Result($qb, static::countModifier(), self::resultFactory());
    }

    protected function qb(?string $alias = null): QueryBuilder
    {
        return $this->connection()->createQueryBuilder()->select('*')->from(static::tableName(), $alias);
    }

    /**
     * Override to define your own count modifier.
     *
     * @return null|callable(QueryBuilder):QueryBuilder
     */
    protected static function countModifier(): ?callable
    {
        return null;
    }

    /**
     * Override to define your own result factory.
     *
     * @return null|callable(array<string,mixed>):V
     */
    protected static function resultFactory(): ?callable
    {
        return null;
    }

    abstract protected static function tableName(): string;

    abstract protected function connection(): Connection;
}
