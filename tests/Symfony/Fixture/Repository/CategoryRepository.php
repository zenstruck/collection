<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Symfony\Fixture\Repository;

use Zenstruck\Collection\Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Symfony\Attributes\ForObject;
use Zenstruck\Collection\Tests\Symfony\Fixture\Entity\Category;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[ForObject(Category::class)]
final class CategoryRepository extends EntityRepository
{
}
