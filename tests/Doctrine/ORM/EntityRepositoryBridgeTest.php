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

use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\ORM\Fixture\EntityRepositoryWithBridge;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class EntityRepositoryBridgeTest extends EntityRepositoryTest
{
    /**
     * @test
     */
    public function can_find_all(): void
    {
        $repo = $this->createWithItems(2);

        $this->assertEquals([$this->expectedValueAt(1), $this->expectedValueAt(2)], $repo->findAll());
    }

    /**
     * @test
     */
    public function find_all_is_empty_if_repository_is_empty(): void
    {
        $this->assertSame([], $this->createWithItems(0)->findAll());
    }

    protected function repo(): EntityRepositoryWithBridge
    {
        return new EntityRepositoryWithBridge($this->em, $this->em->getClassMetadata(Entity::class));
    }
}
