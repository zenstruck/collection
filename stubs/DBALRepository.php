<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\CollectionRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\MatchableRepository;
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
    /** @use CollectionRepository<V> */
    use CollectionRepository;

    /** @use MatchableRepository<V> */
    use MatchableRepository;

    /** @use Paginatable<V> */
    use Paginatable;
}
