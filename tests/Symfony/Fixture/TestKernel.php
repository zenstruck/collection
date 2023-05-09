<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Fixture;

use Composer\InstalledVersions;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Zenstruck\Collection\Symfony\ZenstruckCollectionBundle;
use Zenstruck\Collection\Tests\Symfony\Fixture\Repository\PostRepository;
use Zenstruck\Foundry\ZenstruckFoundryBundle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new ZenstruckFoundryBundle();
        yield new ZenstruckCollectionBundle();
    }

    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader): void
    {
        $c->loadFromExtension('framework', [
            'http_method_override' => false,
            'secret' => 'S3CRET',
            'router' => ['utf8' => true],
            'test' => true,
        ]);

        $c->loadFromExtension('zenstruck_foundry', [
            'auto_refresh_proxies' => false,
        ]);

        $c->loadFromExtension('doctrine', [
            'dbal' => ['url' => 'sqlite:///%kernel.project_dir%/var/data.db'],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'auto_mapping' => true,
                'mappings' => [
                    'Test' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/tests/Symfony/Fixture/Entity',
                        'prefix' => 'Zenstruck\Collection\Tests\Symfony\Fixture\Entity',
                        'alias' => 'Test',
                    ],
                ],
            ],
        ]);

        $c->register('logger', NullLogger::class); // disable logging
        $c->register(Service1::class)
            ->setPublic(true)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;
        $c->register(PostRepository::class)
            ->setAutowired(true)
            ->setAutoconfigured(true)
        ;

        if (\version_compare(InstalledVersions::getVersion('symfony/dependency-injection'), '6.3.0', '>=')) {
            $c->register(Service2::class)
                ->setPublic(true)
                ->setAutowired(true)
                ->setAutoconfigured(true)
            ;
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
    }
}
