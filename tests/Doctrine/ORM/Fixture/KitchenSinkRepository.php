<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Zenstruck\Collection\Doctrine\ORM\Repository\AsEntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsService;
use Zenstruck\Collection\Doctrine\ORM\Repository\IsMatchable;
use Zenstruck\Collection\Doctrine\ORMRepository;
use Zenstruck\Collection\Matchable;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkRepository extends ORMRepository implements Matchable
{
    use AsEntityRepository, AsService, IsMatchable;

    public function getClassName(): string
    {
        return Entity::class;
    }
}
