<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CountableIteratorTest extends IterableCollectionTest
{
    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(new \ArrayIterator($count ? \range(1, $count) : []));
    }
}
