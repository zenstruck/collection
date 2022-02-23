<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Zenstruck\Collection;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsEntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsService;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsCollection;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsFilterable;
use Zenstruck\Collection\Doctrine\ORMRepository;
use Zenstruck\Collection\Filterable;
use Zenstruck\Collection\Paginatable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkRepository extends ORMRepository implements Filterable, Collection
{
    use AsEntityRepository, AsService, IsCollection, IsFilterable, Paginatable;

    public function getClassName(): string
    {
        return Entity::class;
    }
}
