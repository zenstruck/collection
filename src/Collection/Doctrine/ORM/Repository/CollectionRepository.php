<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\Repository;

/**
 * Enables your repository to implement Zenstruck\Collection (and use
 * the Zenstruck\Collection\Paginatable trait).
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait CollectionRepository
{
    final public function take(int $limit, int $offset = 0): Collection
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        return static::createResult($this->qb())->take($limit, $offset);
    }
}
