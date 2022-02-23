<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection\Doctrine\ORM\ObjectRepository;

/**
 * Allows your repository to remove objects from the db.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V
 */
trait Removable
{
    /**
     * @param V $item
     */
    final public function remove(mixed $item, bool $flush = true): static
    {
        if (!$this instanceof ObjectRepository) {
            throw new \BadMethodCallException(\sprintf('"%s" can only be used on instances of "%s".', __TRAIT__, ObjectRepository::class));
        }

        if (!\is_a($item, $this->getClassName())) {
            throw new \InvalidArgumentException(\sprintf('%s::%s() can only be used on entities of type "%s".', static::class, __FUNCTION__, $this->getClassName()));
        }

        $this->em()->remove($item);

        if ($flush) {
            $this->em()->flush();
        }

        return $this;
    }
}
