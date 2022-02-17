<?php

namespace Zenstruck\Collection\Tests\Doctrine\Fixture;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ManagerRegistryStub implements ManagerRegistry
{
    public function __construct(private ?ObjectManager $om)
    {
    }

    public function getDefaultConnectionName()
    {
    }

    public function getConnection($name = null)
    {
    }

    public function getConnections()
    {
    }

    public function getConnectionNames()
    {
    }

    public function getDefaultManagerName()
    {
    }

    public function getManager($name = null)
    {
    }

    public function getManagers()
    {
    }

    public function resetManager($name = null)
    {
    }

    public function getAliasNamespace($alias)
    {
    }

    public function getManagerNames()
    {
    }

    public function getRepository($persistentObject, $persistentManagerName = null)
    {
    }

    public function getManagerForClass($class)
    {
        return $this->om;
    }
}
