<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Repository;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Tests\Doctrine\Fixture\Entity;
use Zenstruck\Collection\Tests\Doctrine\Fixture\ManagerRegistryStub;
use Zenstruck\Collection\Tests\Doctrine\ORM\Fixture\KitchenSinkRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class AsServiceTest extends TestCase
{
    /**
     * @test
     */
    public function manager_registry_is_required(): void
    {
        $repo = new KitchenSinkRepository();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Doctrine was not set');

        $repo->find(1);
    }

    /**
     * @test
     */
    public function entity_must_be_registered(): void
    {
        $repo = new KitchenSinkRepository();
        $repo->setManagerRegistry(new ManagerRegistryStub(null));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('EntityManager for "%s" not found', Entity::class));

        $repo->find(1);
    }
}
