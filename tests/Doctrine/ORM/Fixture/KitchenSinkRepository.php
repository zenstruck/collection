<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsFilterable;
use Zenstruck\Collection\Filterable;
use Zenstruck\Collection\Paginatable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkRepository extends Collection\Doctrine\ORM\EntityRepository implements Filterable, Collection
{
    use IsCollection, IsFilterable, Paginatable;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em, $em->getClassMetadata(Entity::class));
    }
}
