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

use Zenstruck\Collection\Grid;
use Zenstruck\Collection\Symfony\Attributes\ForDefinition;
use Zenstruck\Collection\Symfony\Grid\GridFactory;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Post;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Service3
{
    public function __construct(
        GridFactory $factory,

        #[ForDefinition('grid1')]
        public Grid $grid1,

        #[ForDefinition(Post::class)]
        public Grid $grid2,

        #[ForDefinition('grid3')]
        public Grid $grid3,
    ) {
    }
}
