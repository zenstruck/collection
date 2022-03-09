<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\ORM\Repository\Specification;
use Zenstruck\Collection\Doctrine\ORM\ServiceEntityRepository;
use Zenstruck\Collection\Paginatable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkRepository extends ServiceEntityRepository implements Collection
{
    use IsCollection, Paginatable, Specification;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entity::class);
    }
}
