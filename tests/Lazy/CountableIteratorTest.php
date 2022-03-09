<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CountableIteratorTest extends LazyCollectionTest
{
    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(new \ArrayIterator($count ? \range(1, $count) : []));
    }
}
