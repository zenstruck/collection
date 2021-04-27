<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallableCountableIteratorTest extends IterableCollectionTest
{
    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(fn() => new \ArrayIterator($count ? \range(1, $count) : []));
    }
}
