<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ClosureArrayTest extends LazyCollectionTest
{
    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(fn() => $count ? \range(1, $count) : []);
    }
}
