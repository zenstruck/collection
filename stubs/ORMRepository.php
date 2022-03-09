<?php

use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\Specification;

/**
 * @template V of object
 * @extends EntityRepository<V>
 */
abstract class ORMRepository extends EntityRepository
{
    /** @use Specification<V> */
    use Specification;
}
