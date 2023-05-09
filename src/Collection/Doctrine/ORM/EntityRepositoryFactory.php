<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\ORM;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class EntityRepositoryFactory implements ObjectRepositoryFactory
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    public function create(string $class): ObjectRepository
    {
        $em = $this->registry->getManagerForClass($class);

        if (!$em instanceof EntityManagerInterface) {
            throw new \LogicException($em ? \sprintf('"%s" only supports "%s"," %s" given.', self::class, EntityManagerInterface::class, $em::class) : \sprintf('No entity manager found for class "%s".', $class));
        }

        return new EntityRepository($em, $class);
    }
}
