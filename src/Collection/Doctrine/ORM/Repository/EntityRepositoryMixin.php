<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository;

/**
 * Allows your repository to have access to Doctrine\ORM\EntityRepository
 * methods (except count()).
 *
 * @mixin EntityRepository
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait EntityRepositoryMixin
{
    final public function __call($name, $arguments)
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        return $this->repo()->{$name}(...$arguments);
    }
}
