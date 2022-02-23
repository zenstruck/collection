<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsFilterable;
use Zenstruck\Collection\Filterable;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends ObjectRepository<V>
 * @implements Collection<int,V>
 * @implements Filterable<int,V>
 */
abstract class DBALRepository extends ObjectRepository implements Collection, Filterable
{
    /** @use IsCollection<V> */
    use IsCollection;

    /** @use IsFilterable<V> */
    use IsFilterable;

    /** @use Paginatable<V> */
    use Paginatable;
}
