<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Fixture\Grid;

use Zenstruck\Collection\Grid\GridBuilder;
use Zenstruck\Collection\Grid\GridDefinition;
use Zenstruck\Collection\Specification\Filter\GreaterThan;
use Zenstruck\Collection\Symfony\Attributes\AsGrid;
use Zenstruck\Collection\Symfony\Attributes\ForObject;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Post;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ForObject(Post::class)]
#[AsGrid('grid3')]
final class Grid3Definition implements GridDefinition
{
    public function configure(GridBuilder $builder): void
    {
        $builder->addColumn('id');
        $builder->defaultSpecification = new GreaterThan('id', 2);
    }
}
