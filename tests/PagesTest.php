<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\CallbackCollection;
use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Pages;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PagesTest extends TestCase
{
    /**
     * @test
     */
    public function counting_does_not_load_the_first_page(): void
    {
        $iterations = 0;
        $collection = new CallbackCollection(
            static function() use (&$iterations) {
                ++$iterations;

                yield from \range(1, 10);
            },
            static fn() => 10,
        );

        $this->assertCount(4, new Pages($collection, 3));
        $this->assertSame(0, $iterations);
    }

    /**
     * @test
     */
    public function iterating_only_counts_the_collection_once(): void
    {
        $counts = 0;
        $collection = new CallbackCollection(
            static fn() => yield from \range(1, 10),
            static function() use (&$counts) {
                ++$counts;

                return 10;
            },
        );

        $pages = new Pages($collection, 3);

        $this->assertCount(4, \iterator_to_array($pages));
        $this->assertSame(1, $counts);
    }

    /**
     * @test
     */
    public function can_count_and_iterate_empty_collection(): void
    {
        $collection = new Pages(new LazyCollection());

        $this->assertEmpty($collection);
        $this->assertEmpty(\iterator_to_array($collection));
    }

    /**
     * @test
     */
    public function can_count_and_iterate_single_page_collection(): void
    {
        $collection = new Pages(new LazyCollection(\range(1, 6)));
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
        $collection = new Pages(new LazyCollection(\range(1, 71)));
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
        $collection = new Pages(new LazyCollection());

        $this->assertSame([], \iterator_to_array($collection->get(1)));
        $this->assertSame([], \iterator_to_array($collection->get(99)));
        $this->assertCount(0, $collection->get(1));
        $this->assertCount(0, $collection->get(99));
        $this->assertSame(1, $collection->get(1)->pageCount());
        $this->assertSame(1, $collection->get(99)->pageCount());
        $this->assertSame(1, $collection->get(1)->currentPage());
        $this->assertSame(99, $collection->get(99)->currentPage());
        $this->assertSame(1, $collection->get(99)->strict()->currentPage());
        $this->assertNull($collection->get(1)->nextPage());
        $this->assertNull($collection->get(1)->previousPage());
        $this->assertNull($collection->get(99)->strict()->nextPage());
        $this->assertSame(100, $collection->get(99)->nextPage());
        $this->assertNull($collection->get(99)->strict()->previousPage());
        $this->assertSame(98, $collection->get(99)->previousPage());
    }
}
