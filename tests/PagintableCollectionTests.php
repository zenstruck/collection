<?php

namespace Zenstruck\Collection\Tests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait PagintableCollectionTests
{
    use CollectionTests;

    /**
     * @test
     */
    public function can_paginate(): void
    {
        $collection = $this->createWithItems(11);

        $pager = $collection->paginate();

        $this->assertCount(11, $pager);
        $this->assertEquals($this->expectedValueAt(1), \array_values(\iterator_to_array($collection))[0]);
        $this->assertEquals($this->expectedValueAt(5), \array_values(\iterator_to_array($collection))[4]);
        $this->assertEquals($this->expectedValueAt(11), \array_values(\iterator_to_array($collection))[10]);

        $pager = $collection->paginate(2, 10);

        $this->assertCount(1, $pager);
        $this->assertEquals($this->expectedValueAt(11), \array_values(\iterator_to_array($pager))[0]);
    }

    /**
     * @test
     */
    public function can_get_pages(): void
    {
        $collection = $this->createWithItems(11);

        $this->assertCount(4, $collection->pages(3));
        $this->assertCount(2, $collection->pages(6));
        $this->assertCount(1, $collection->pages());
        $this->assertCount(2, $collection->pages(10));

        $pages = \iterator_to_array($collection->pages(10));

        $this->assertCount(10, $pages[0]);
        $this->assertCount(1, $pages[1]);
        $this->assertEquals($this->expectedValueAt(1), \array_values(\iterator_to_array($pages[0]))[0]);
        $this->assertEquals($this->expectedValueAt(10), \array_values(\iterator_to_array($pages[0]))[9]);
        $this->assertEquals($this->expectedValueAt(11), \array_values(\iterator_to_array($pages[1]))[0]);
    }
}
