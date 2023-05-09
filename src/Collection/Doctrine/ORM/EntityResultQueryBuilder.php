<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\QueryBuilder;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
final class EntityResultQueryBuilder extends QueryBuilder
{
    /**
     * @return EntityResult<V>
     */
    public function result(): EntityResult
    {
        return new EntityResult($this);
    }
}
