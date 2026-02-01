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
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Zenstruck\Collection\Doctrine\Grid\ObjectGridDefinition;
use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Grid\GridDefinition;
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

        $builder->registerAttributeForAutoconfiguration(AsGrid::class, static function(ChildDefinition $definition, AsGrid $attribute) {
            $definition->addTag('zenstruck_collection.grid_definition', ['key' => $attribute->name]);
        });

        if (isset($builder->getParameter('kernel.bundles')['DoctrineBundle'])) { // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $loader->load('doctrine.php');

            $builder->registerAttributeForAutoconfiguration(ForObject::class, static function(ChildDefinition $definition, ForObject $attribute, \ReflectionClass $class) { // @phpstan-ignore argument.type
                if ($class->implementsInterface(GridDefinition::class)) {
                    $definition->addTag('zenstruck_collection.grid_definition', ['key' => $attribute->class, 'as_object' => true]);

                    return;
                }

                if (!$class->isSubclassOf(EntityRepository::class)) {
                    throw new LogicException(\sprintf('Can only use "%s" on classes that implement "%s" or extend "%s".', ForObject::class, GridDefinition::class, EntityRepository::class));
                }

                if (EntityRepository::class !== $class->getConstructor()?->getDeclaringClass()->name) {
                    throw new LogicException(\sprintf('Cannot use "%s" on "%s" as it overrides the constructor.', ForObject::class, $class->name));
                }

                $definition->setArgument('$class', $attribute->class);
            });
        }
    }

    public function process(ContainerBuilder $container): void
    {
        if (!isset($container->getParameter('kernel.bundles')['DoctrineBundle'])) { // @phpstan-ignore offsetAccess.nonOffsetAccessible
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

                if ($gridTag = collect($tags)->find(static fn(array $t) => false === ($t['as_object'] ?? false))) {
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
