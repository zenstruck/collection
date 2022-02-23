<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsFilterable;
use Zenstruck\Collection\Doctrine\ORMResult;
use Zenstruck\Collection\Doctrine\ORMRepository as BaseORMRepository;
use Zenstruck\Collection\Paginatable;

/**
 * @template V of object
 * @extends BaseORMRepository<V>
 * @implements Collection<int,V>
 */
abstract class ORMRepository extends BaseORMRepository implements Collection
{
    /** @use IsFilterable<V,ORMResult> */
    use IsFilterable;

    /** @use Paginatable<V> */
    use Paginatable;
}
