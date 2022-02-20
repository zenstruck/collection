<?php

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsMatchable;
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
    /** @use IsMatchable<V,ORMResult> */
    use IsMatchable { matchOne as private; }

    /** @use Paginatable<V> */
    use Paginatable;

    /**
     * @return V
     */
    public function get(mixed $specification): object
    {
        return $this->matchOne($specification);
    }
}
