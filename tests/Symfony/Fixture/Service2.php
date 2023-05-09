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

use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Symfony\Doctrine\ForObject;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Category;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Service2
{
    public function __construct(
        #[ForObject(Category::class)]
        public ObjectRepository $categoryRepo,
    ) {
    }
}
