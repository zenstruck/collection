<?php

use Zenstruck\Collection\Doctrine\ORM\Repository\IsMatchable;
use Zenstruck\Collection\Doctrine\ORMResult;
use Zenstruck\Collection\Doctrine\ORMRepository as BaseORMRepository;

/**
 * @template V of object
 * @extends BaseORMRepository<V>
 */
abstract class ORMRepository extends BaseORMRepository
{
    /** @use IsMatchable<V,ORMResult> */
    use IsMatchable { matchOne as private; }

    /**
     * @return V
     */
    public function get(mixed $specification): object
    {
        return $this->matchOne($specification);
    }
}
