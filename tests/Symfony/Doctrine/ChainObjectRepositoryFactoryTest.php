<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Doctrine;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;
use Zenstruck\Collection\Symfony\Doctrine\ChainObjectRepositoryFactory;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChainObjectRepositoryFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function repositories_are_cached_per_class(): void
    {
        $inner = $this->createMock(ObjectRepositoryFactory::class);
        $inner->expects($this->once())
            ->method('create')
            ->with(Entity::class)
            ->willReturn($this->createMock(ObjectRepository::class))
        ;

        $factory = new ChainObjectRepositoryFactory($inner);

        $this->assertSame($factory->create(Entity::class), $factory->create(Entity::class));
    }

    /**
     * @test
     */
    public function resetting_clears_the_cache(): void
    {
        $inner = $this->createMock(ObjectRepositoryFactory::class);
        $inner->expects($this->exactly(2))
            ->method('create')
            ->willReturnCallback(fn() => $this->createMock(ObjectRepository::class))
        ;

        $factory = new ChainObjectRepositoryFactory($inner);
        $first = $factory->create(Entity::class);

        $factory->reset();

        $this->assertNotSame($first, $factory->create(Entity::class));
    }
}
