<?php

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * For use with Symfony/DoctrineBundle. Allows your repositories to
 * be services.
 *
 * @see \Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @template V of object
 * @extends EntityRepository<V>
 */
class ServiceEntityRepository extends EntityRepository implements ServiceEntityRepositoryInterface
{
    /**
     * @param class-string<V> $class The class name of the entity this repository manages
     */
    public function __construct(ManagerRegistry $registry, string $class)
    {
        $manager = $registry->getManagerForClass($class);

        if (!$manager instanceof EntityManagerInterface) {
            throw new \LogicException(\sprintf('Could not find the entity manager for class "%s". Check your Doctrine configuration to make sure it is configured to load this entity’s metadata.', $class));
        }

        parent::__construct($manager, $manager->getClassMetadata($class));
    }
}
