<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\DBAL\Repository\Specification;
use Zenstruck\Collection\Paginatable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends ObjectRepository<V>
 * @implements Collection<int,V>
 */
abstract class DBALRepository extends ObjectRepository implements Collection
{
    /** @use IsCollection<V> */
    use IsCollection;

    /** @use Specification<V> */
    use Specification;

    /** @use Paginatable<V> */
    use Paginatable;
}
