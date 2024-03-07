<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;
use Zenstruck\Collection\Doctrine\ORM\EntityRepositoryFactory;
use Zenstruck\Collection\Symfony\Doctrine\ChainObjectRepositoryFactory;

return static function(ContainerConfigurator $configurator) {
    $configurator->services()
        ->set('.zenstruck_collection.doctrine.orm.object_repo_factory', EntityRepositoryFactory::class)
            ->args([service('doctrine')])

        ->set('.zenstruck_collection.doctrine.chain_object_repo_factory', ChainObjectRepositoryFactory::class)
            ->args([service('.zenstruck_collection.doctrine.orm.object_repo_factory')])
            ->tag('kernel.reset', ['method' => 'reset'])

        ->alias(ObjectRepositoryFactory::class, '.zenstruck_collection.doctrine.chain_object_repo_factory')
    ;
};
