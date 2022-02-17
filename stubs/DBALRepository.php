<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsMatchable;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends ObjectRepository<V>
 * @implements Collection<int,V>
 * @implements Matchable<int,V>
 */
abstract class DBALRepository extends ObjectRepository implements Collection, Matchable
{
    /** @use IsCollection<V> */
    use IsCollection;

    /** @use IsMatchable<V> */
    use IsMatchable;

    /** @use Paginatable<V> */
    use Paginatable;
}
