<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Zenstruck\Collection\Symfony\Grid\GridFactory;

return static function(ContainerConfigurator $configurator) {
    $configurator->services()
        ->set('.zenstruck_collection.grid_factory', GridFactory::class)
            ->args([
                tagged_locator('zenstruck_collection.grid_definition', indexAttribute: 'key'),
            ])

        ->alias(GridFactory::class, '.zenstruck_collection.grid_factory')
    ;
};
