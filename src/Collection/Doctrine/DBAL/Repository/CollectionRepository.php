<?php

namespace Zenstruck\Collection\Doctrine\DBAL\Repository;

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\DBAL\Repository;

/**
 * Enables your repository to implement Zenstruck\Collection (and use
 * the Zenstruck\Collection\Paginatable trait).
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 */
trait CollectionRepository
{
    /**
     * @return Collection<int,Value>
     */
    final public function take(int $limit, int $offset = 0): Collection
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        return static::createResult($this->qb())->take($limit, $offset);
    }
}
