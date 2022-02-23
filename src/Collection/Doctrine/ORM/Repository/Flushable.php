<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;

/**
 * Allows your repository to flush pending changes to the db.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait Flushable
{
    final public function flush(): static
    {
        if (!$this instanceof ObjectRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, ObjectRepository::class));
        }

        $this->em()->flush();

        return $this;
    }
}
