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
use Zenstruck\Collection\Symfony\Attributes\AsGrid;
use Zenstruck\Collection\Tests\Symfony\Fixture\Repository\PostRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsGrid('grid1')]
final class Grid1Definition implements GridDefinition
{
    public function __construct(private PostRepository $repository)
    {
    }

    public function configure(GridBuilder $builder): void
    {
        $builder->source = $this->repository;
        $builder->addColumn('id');
    }
}
