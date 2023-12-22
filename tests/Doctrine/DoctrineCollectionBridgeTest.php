<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Doctrine;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\DoctrineCollectionBridge;
use Zenstruck\Collection\Tests\CollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DoctrineCollectionBridgeTest extends TestCase
{
    use CollectionTests;

    protected function createWithItems(int $count): DoctrineCollectionBridge
    {
        return new DoctrineCollectionBridge($count ? \range(1, $count) : []);
    }
}
