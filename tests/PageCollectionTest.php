<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\PageCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PageCollectionTest extends TestCase
{
    /**
     * @test
     */
    public function can_count_and_iterate_empty_collection(): void
    {
        $collection = new PageCollection(new LazyCollection());

        $this->assertEmpty($collection);
        $this->assertEmpty(\iterator_to_array($collection));
    }

    /**
     * @test
     */
    public function can_count_and_iterate_single_page_collection(): void
    {
        $collection = new PageCollection(new LazyCollection(\range(1, 6)));
        $pages = \iterator_to_array($collection);

        $this->assertCount(1, $collection);
        $this->assertCount(1, $pages);
        $this->assertSame(6, $pages[0]->count());
        $this->assertCount(6, \iterator_to_array($pages[0]));
    }

    /**
     * @test
     */
    public function can_count_and_iterate_multi_page_collection(): void
    {
        $collection = new PageCollection(new LazyCollection(\range(1, 71)));
        $pages = \iterator_to_array($collection);

        $this->assertCount(4, $collection);
        $this->assertCount(4, $pages);
        $this->assertSame(20, $pages[0]->count());
        $this->assertCount(20, \iterator_to_array($pages[0]));
        $this->assertSame(20, $pages[1]->count());
        $this->assertCount(20, \iterator_to_array($pages[1]));
        $this->assertSame(20, $pages[2]->count());
        $this->assertCount(20, \iterator_to_array($pages[2]));
        $this->assertSame(11, $pages[3]->count());
        $this->assertCount(11, \iterator_to_array($pages[3]));
    }

    /**
     * @test
     */
    public function can_always_get_page(): void
    {
        $collection = new PageCollection(new LazyCollection());

        $this->assertSame([], \iterator_to_array($collection->get(1)));
        $this->assertSame([], \iterator_to_array($collection->get(99)));
        $this->assertCount(0, $collection->get(1));
        $this->assertCount(0, $collection->get(99));
        $this->assertSame(1, $collection->get(1)->pageCount());
        $this->assertSame(1, $collection->get(99)->pageCount());
        $this->assertSame(1, $collection->get(1)->currentPage());
        $this->assertSame(1, $collection->get(99)->currentPage());
        $this->assertNull($collection->get(1)->nextPage());
        $this->assertNull($collection->get(1)->previousPage());
        $this->assertNull($collection->get(99)->nextPage());
        $this->assertNull($collection->get(99)->previousPage());
    }
}
