<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Symfony;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zenstruck\Collection\Doctrine\Grid\ObjectGridDefinition;
use Zenstruck\Collection\Symfony\Attributes\AsGrid;
use Zenstruck\Collection\Symfony\Attributes\ForObject;

use function Zenstruck\collect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @codeCoverageIgnore
 */
final class ZenstruckCollectionBundle extends AbstractBundle implements CompilerPassInterface
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass($this);
    }

    public function getPath(): string
    {
        return __DIR__.'/../../../';
    }

    /**
     * @param mixed[] $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $loader = new PhpFileLoader($builder, new FileLocator(__DIR__.'/../../../config/symfony'));

        $loader->load('grid.php');

        $builder->registerAttributeForAutoconfiguration(AsGrid::class, function(ChildDefinition $definition, AsGrid $attribute) {
            $definition->addTag('zenstruck_collection.grid_definition', ['key' => $attribute->name]);
        });

        if (isset($builder->getParameter('kernel.bundles')['DoctrineBundle'])) {
            $loader->load('doctrine.php');

            $builder->registerAttributeForAutoconfiguration(ForObject::class, function(ChildDefinition $definition, ForObject $attribute) {
                $definition->addTag('zenstruck_collection.grid_definition', ['key' => $attribute->class, 'as_object' => true]);
            });
        }
    }

    public function process(ContainerBuilder $container): void
    {
        if (!isset($container->getParameter('kernel.bundles')['DoctrineBundle'])) {
            return;
        }

        foreach ($container->findTaggedServiceIds('zenstruck_collection.grid_definition') as $id => $tags) {
            foreach ($tags as $tag) {
                if (!($tag['as_object'] ?? false)) {
                    continue;
                }

                $container->register($id.'.object', ObjectGridDefinition::class)
                    ->setDecoratedService($id)
                    ->setArguments([
                        $tag['key'],
                        new Reference('.zenstruck_collection.doctrine.chain_object_repo_factory'),
                        new Reference($id.'.object.inner'),
                    ])
                ;

                if ($gridTag = collect($tags)->find(fn(array $t) => false === ($t['as_object'] ?? false))) {
                    // service was also tagged using AsGrid - use it as the "alias"
                    $container->getDefinition($id)
                        ->clearTag('zenstruck_collection.grid_definition')
                        ->addTag('zenstruck_collection.grid_definition', ['key' => $gridTag['key']])
                    ;
                }
            }
        }
    }
}
