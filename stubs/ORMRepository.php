<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\Specification;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Paginatable;

/**
 * @template V of object
 * @extends EntityRepository<V>
 * @implements Collection<int,V>
 */
abstract class ORMRepository extends EntityRepository implements Collection
{
    /** @use Specification<V> */
    use Specification;

    /** @use Paginatable<V> */
    use Paginatable;

    /** @use IsCollection<V> */
    use IsCollection;
}
