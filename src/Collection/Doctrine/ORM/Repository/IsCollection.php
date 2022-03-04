<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;

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
        if (!$this instanceof EntityRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, EntityRepository::class));
        }

        return static::resultFor($this->createQueryBuilder('e'))->take($limit, $offset);
    }
}
