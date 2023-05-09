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

use Zenstruck\Collection\Doctrine\ObjectRepositoryFactory;
use Zenstruck\Collection\Tests\Symfony\Fixture\Repository\PostRepository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Service1
{
    public function __construct(public ObjectRepositoryFactory $factory, public PostRepository $postRepo)
    {
    }
}
