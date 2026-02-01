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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
final class EntityResultQueryBuilder extends QueryBuilder
{
    /** @var list<callable(Query):void> */
    private array $queryModifiers = [];
    private bool $readonly = false;

    /**
     * @param class-string<V> $class
     *
     * @return self<V>
     */
    public static function forEntity(EntityManagerInterface $em, string $class, string $alias, ?string $indexBy = null): self
    {
        return (new self($em))
            ->select($alias)
            ->from($class, $alias, $indexBy)
        ;
    }

    /**
     * @return EntityResult<V>
     */
    public function result(): EntityResult
    {
        $result = new EntityResult($this);

        return $this->readonly ? $result->readonly() : $result;
    }

    public function getQuery(): Query
    {
        $query = parent::getQuery();

        foreach ($this->queryModifiers as $modifier) {
            $modifier($query);
        }

        return $query;
    }

    /**
     * Add a query modifier.
     *
     * @param callable(Query):void $modifier
     *
     * @return $this
     */
    public function modifyQuery(callable $modifier): self
    {
        $this->queryModifiers[] = $modifier;

        return $this;
    }

    /**
     * Mark the query and {@see EntityResult} as readonly.
     *
     * @return $this
     */
    public function readonly(): self
    {
        $this->readonly = true;

        return $this->modifyQuery(static function(Query $query) {
            $query->setHint(Query::HINT_READ_ONLY, true);
        });
    }

    /**
     * @return $this
     */
    public function cacheResult(?int $lifetime = null, ?string $key = null): self
    {
        return $this->modifyQuery(static function(Query $query) use ($lifetime, $key) {
            $query->enableResultCache($lifetime, $key);
        });
    }
}
