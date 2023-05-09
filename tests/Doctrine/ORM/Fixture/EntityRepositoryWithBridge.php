<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine\ORM\Fixture;

use Doctrine\ORM\EntityRepository;
use Zenstruck\Collection\Doctrine\ObjectRepository;
use Zenstruck\Collection\Doctrine\ORM\EntityRepositoryBridge;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class EntityRepositoryWithBridge extends EntityRepository implements ObjectRepository
{
    use EntityRepositoryBridge;
}
