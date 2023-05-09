<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepositoryFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class EntityRepositoryFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function create_for_class(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(\stdClass::class)
            ->willReturn($em)
        ;

        $factory = new EntityRepositoryFactory($registry);

        $this->assertInstanceOf(EntityRepository::class, $factory->create(\stdClass::class));
    }

    /**
     * @test
     */
    public function invalid_object_manager_class(): void
    {
        $om = $this->createMock(ObjectManager::class);
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(\stdClass::class)
            ->willReturn($om)
        ;

        $factory = new EntityRepositoryFactory($registry);

        $this->expectException(\LogicException::class);

        $factory->create(\stdClass::class);
    }

    /**
     * @test
     */
    public function no_object_manager_for_class(): void
    {
        $registry = $this->createMock(ManagerRegistry::class);
        $registry
            ->expects($this->once())
            ->method('getManagerForClass')
            ->with(\stdClass::class)
            ->willReturn(null)
        ;

        $factory = new EntityRepositoryFactory($registry);

        $this->expectException(\LogicException::class);

        $factory->create(\stdClass::class);
    }
}
