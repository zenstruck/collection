<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DummyManagerRegistry implements ManagerRegistry
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function getDefaultConnectionName(): string
    {
    }

    public function getConnection($name = null): object
    {
    }

    public function getConnections(): array
    {
    }

    public function getConnectionNames(): array
    {
    }

    public function getDefaultManagerName(): string
    {
    }

    public function getManager($name = null): ObjectManager
    {
    }

    public function getManagers(): array
    {
    }

    public function resetManager($name = null): ObjectManager
    {
    }

    public function getAliasNamespace($alias): string
    {
    }

    public function getManagerNames(): array
    {
    }

    public function getRepository($persistentObject, $persistentManagerName = null): ObjectRepository
    {
    }

    public function getManagerForClass($class): ?ObjectManager
    {
        return $this->em;
    }
}
