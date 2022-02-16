<?php

namespace Zenstruck\Collection\Doctrine\ORM\Repository;

use Zenstruck\Collection\Doctrine\ORM\Repository;

/**
 * Allows your repository to add objects to the db.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template Value
 */
trait Writable
{
    /**
     * @param Value $item
     */
    final public function add(object $item, bool $flush = true): static
    {
        if (!$this instanceof Repository) {
            throw new \BadMethodCallException(); // todo
        }

        if (!\is_a($item, $this->getClassName())) {
            throw new \InvalidArgumentException(); // todo
        }

        $this->em()->persist($item);

        if ($flush) {
            $this->em()->flush();
        }

        return $this;
    }
}
