<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Zenstruck\Collection\Doctrine\ORM\Repository\AsEntityRepository;
use Zenstruck\Collection\Doctrine\ORM\Repository\AsService;
use Zenstruck\Collection\Doctrine\ORMRepository;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class KitchenSinkRepository extends ORMRepository
{
    use AsEntityRepository, AsService;

    public function getClassName(): string
    {
        return Entity::class;
    }
}
