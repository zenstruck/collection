<?php

use Doctrine\ORM\QueryBuilder;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsEntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\Flushable;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsMatchable;
use Zenstruck\Collection\Doctrine\ORM\Repository\Removable;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsService;
use Zenstruck\Collection\Doctrine\ORM\Repository\Writable;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @extends ObjectRepository<V>
 * @implements Collection<int,V>
 * @implements Matchable<int,V>
 */
abstract class ORMRepository extends ObjectRepository implements Collection, Matchable
{
    /** @use IsCollection<V> */
    use IsCollection;
    use AsEntityRepository;
    use Flushable;
    use AsService;

    /** @use IsMatchable<V, ORMResult> */
    use IsMatchable;

    /** @use Paginatable<V> */
    use Paginatable;

    /** @use Removable<V> */
    use Removable;

    /** @use Writable<V> */
    use Writable;

    /**
     * @return ORMResult<V>
     */
    protected static function createResult(QueryBuilder $qb, bool $fetchCollection = true, ?bool $useOutputWalkers = null): ORMResult
    {
        return new ORMResult($qb, $fetchCollection, $useOutputWalkers);
    }
}
