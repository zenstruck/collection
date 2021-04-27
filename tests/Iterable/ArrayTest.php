<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayTest extends IterableCollectionTest
{
    /**
     * @test
     */
    public function can_use_negative_values_for_take_page(): void
    {
        $collection = new IterableCollection(['a', 'b', 'c', 'd', 'e']);

        $this->assertSame(['a', 'b'], \array_values(\iterator_to_array($collection->take(-3))));
        $this->assertSame(['c', 'd'], \array_values(\iterator_to_array($collection->take(2, -3))));
    }

    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection($count ? \range(1, $count) : []);
    }
}
