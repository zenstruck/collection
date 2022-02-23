<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;

/**
 * Enables your repository to implement {@see Collection} (and use
 * the {@see Collection\Paginatable} trait).
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
trait IsCollection
{
    /**
     * @return Collection<int,V>
     */
    final public function take(int $limit, int $offset = 0): Collection
    {
        if (!$this instanceof ObjectRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, ObjectRepository::class));
        }

        return static::createResult($this->qb())->take($limit, $offset);
    }
}
