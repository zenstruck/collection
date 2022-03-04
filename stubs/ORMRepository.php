<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsFilterable;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Paginatable;

/**
 * @template V of object
 * @extends EntityRepository<V>
 * @implements Collection<int,V>
 */
abstract class ORMRepository extends EntityRepository implements Collection
{
    /** @use IsFilterable<V> */
    use IsFilterable;

    /** @use Paginatable<V> */
    use Paginatable;

    /** @use IsCollection<V> */
    use IsCollection;
}
