<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ClosureCountableIteratorTest extends LazyCollectionTest
{
    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(fn() => new \ArrayIterator($count ? \range(1, $count) : []));
    }
}
