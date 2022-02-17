<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection\Doctrine\ORM\Repository;

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
    final public function remove(object $item, bool $flush = true): static
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        if (!\is_a($item, $this->getClassName())) {
            throw new \InvalidArgumentException(); // todo
        }

        $this->em()->remove($item);

        if ($flush) {
            $this->em()->flush();
        }

        return $this;
    }
}
