<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayTest extends LazyCollectionTest
{
    /**
     * @test
     */
    public function can_use_negative_values_for_take_page(): void
    {
        $collection = new LazyCollection(['a', 'b', 'c', 'd', 'e']);

        $this->assertSame(['a', 'b'], \array_values(\iterator_to_array($collection->take(-3))));
        $this->assertSame(['c', 'd'], \array_values(\iterator_to_array($collection->take(2, -3))));
    }

    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection($count ? \range(1, $count) : []);
    }
}
