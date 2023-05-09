<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Symfony\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;
use Zenstruck\Collection\Doctrine\ORM\EntityRepositoryFactory;
use Zenstruck\Collection\Symfony\Doctrine\ChainObjectRepositoryFactory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @codeCoverageIgnore
 */
final class ZenstruckCollectionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        if (!isset($container->getParameter('kernel.bundles')['DoctrineBundle'])) {
            return;
        }

        $container->register('zenstruck_collection.doctrine.orm.object_repo_factory', EntityRepositoryFactory::class)
            ->addArgument(new Reference('doctrine'))
        ;

        $container->register('zenstruck_collection.doctrine.chain_object_repo_factory', ChainObjectRepositoryFactory::class)
            ->addArgument(new Reference('zenstruck_collection.doctrine.orm.object_repo_factory'))
            ->addTag('kernel.reset', ['method' => 'reset'])
        ;

        $container->setAlias(ObjectRepositoryFactory::class, 'zenstruck_collection.doctrine.chain_object_repo_factory');
    }
}
