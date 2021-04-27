<?php

namespace Zenstruck\Collection\Tests;

use Zenstruck\Collection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait CollectionTests
{
    use CountableIteratorTests;

    /**
     * @test
     */
    public function can_take_page(): void
    {
        $collection = $this->createWithItems(11)->take(2);

        $this->assertCount(2, $collection);
        $this->assertEquals($this->expectedValueAt(1), \array_values(\iterator_to_array($collection))[0]);
        $this->assertEquals($this->expectedValueAt(2), \array_values(\iterator_to_array($collection))[1]);

        $collection = $this->createWithItems(11)->take(2, 3);

        $this->assertCount(2, $collection);
        $this->assertEquals($this->expectedValueAt(4), \array_values(\iterator_to_array($collection))[0]);
        $this->assertEquals($this->expectedValueAt(5), \array_values(\iterator_to_array($collection))[1]);

        $collection = $this->createWithItems(11)->take(50, 99);

        $this->assertEmpty($collection);

        $collection = $this->createWithItems(11)->take(0, 3);

        $this->assertEmpty($collection);
    }

    abstract protected function createWithItems(int $count): Collection;
}
