<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
final class ResultQueryBuilder extends QueryBuilder
{
    /**
     * @return Result<V>
     */
    public function result(): Result
    {
        return new Result($this);
    }
}
