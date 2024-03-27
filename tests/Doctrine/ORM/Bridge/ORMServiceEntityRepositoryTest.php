<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Bridge;

use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Collection\Doctrine\ORM\Bridge\ORMServiceEntityRepository;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ORMServiceEntityRepositoryTest extends ORMEntityRepositoryTest
{
    protected function repo(): ORMServiceEntityRepository
    {
        $manager = $this->createMock(ManagerRegistry::class);
        $manager->method('getManagerForClass')->willReturn($this->em);

        return new ORMServiceEntityRepository($manager, Entity::class);
    }
}
