<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Doctrine\Grid;

use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;
use Zenstruck\Collection\Grid\GridBuilder;
use Zenstruck\Collection\Grid\GridDefinition;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @implements GridDefinition<object>
 */
final class ObjectGridDefinition implements GridDefinition
{
    /**
     * @param class-string<object>   $class
     * @param GridDefinition<object> $inner
     */
    public function __construct(
        private string $class,
        private ObjectRepositoryFactory $repositoryFactory,
        private GridDefinition $inner,
    ) {
    }

    public function configure(GridBuilder $builder): void
    {
        $this->inner->configure($builder);

        if ($builder->source) {
            return;
        }

        $builder->source = $this->repositoryFactory->create($this->class);
    }
}
