<?php

use Zenstruck\Collection\Doctrine\DBAL\ObjectRepository;
use Zenstruck\Collection\Doctrine\DBAL\Repository\Specification;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 * @extends ObjectRepository<V>
 */
abstract class DBALRepository extends ObjectRepository
{
    /** @use Specification<V> */
    use Specification;
}
