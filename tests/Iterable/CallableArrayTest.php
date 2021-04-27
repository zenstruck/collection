<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallableArrayTest extends IterableCollectionTest
{
    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(fn() => $count ? \range(1, $count) : []);
    }
}
