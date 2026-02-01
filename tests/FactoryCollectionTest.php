<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\FactoryCollection;
use Zenstruck\Collection\LazyCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FactoryCollectionTest extends TestCase
{
    use CollectionTests;

    protected function createWithItems(int $count): Collection
    {
        return new FactoryCollection(new LazyCollection($count ? \range(1, $count) : []), static fn($position) => "value {$position}");
    }

    protected function expectedValueAt(int $position): string
    {
        return "value {$position}";
    }
}
