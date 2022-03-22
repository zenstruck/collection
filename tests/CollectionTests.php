<?php

namespace Zenstruck\Collection\Tests;

use Zenstruck\Collection;
use Zenstruck\Collection\Tests\Fixture\Stringable;

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

    /**
     * @test
     */
    public function filter(): void
    {
        $items = $this->createWithItems(3);
        $arr = \iterator_to_array($items);

        $this->assertEquals([1 => $arr[1], 2 => $arr[2]], \iterator_to_array($items->filter(fn($value, $key) => $key > 0)));
    }

    /**
     * @test
     */
    public function reject(): void
    {
        $items = $this->createWithItems(3);
        $arr = \iterator_to_array($items);

        $this->assertEquals([0 => $arr[0]], \iterator_to_array($items->reject(fn($value, $key) => $key > 0)));
    }

    /**
     * @test
     */
    public function key_by(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(['k0', 'k1', 'k2'], \array_keys(\iterator_to_array($items->keyBy(fn($value, $key) => 'k'.$key))));
    }

    /**
     * @test
     */
    public function key_by_stringable_key(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(['k0', 'k1', 'k2'], \array_keys(\iterator_to_array($items->keyBy(
            fn($value, $key) => new Stringable('k'.$key)
        ))));
    }

    /**
     * @test
     */
    public function map(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(['v0', 'v1', 'v2'], \iterator_to_array($items->map(fn($value, $key) => 'v'.$key)));
    }

    /**
     * @test
     */
    public function map_with_keys(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(
            ['k0' => 'v0', 'k1' => 'v1', 'k2' => 'v2'],
            \iterator_to_array($items->mapWithKeys(fn($value, $key) => yield 'k'.$key => 'v'.$key))
        );
    }

    /**
     * @test
     */
    public function map_with_keys_stringable_key(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(
            ['k0' => 'v0', 'k1' => 'v1', 'k2' => 'v2'],
            \iterator_to_array($items->mapWithKeys(fn($value, $key) => yield new Stringable('k'.$key) => 'v'.$key))
        );
    }

    /**
     * @test
     */
    public function first(): void
    {
        $items = $this->createWithItems(2);

        $this->assertEquals(\iterator_to_array($items)[0], $items->first());
        $this->assertEquals(\iterator_to_array($items)[0], $items->first('foo'));
        $this->assertNull($this->createWithItems(0)->first());
        $this->assertEquals('foo', $this->createWithItems(0)->first('foo'));
    }

    /**
     * @test
     */
    public function first_where(): void
    {
        $items = $this->createWithItems(2);

        $this->assertEquals(\iterator_to_array($items)[1], $items->firstWhere(fn($value, $key) => $key > 0));
        $this->assertEquals(\iterator_to_array($items)[1], $items->firstWhere(fn($value, $key) => $key > 0), 'foo');
        $this->assertNull($items->firstWhere(fn($value, $key) => $key > 10));
        $this->assertEquals('foo', $items->firstWhere(fn($value, $key) => $key > 10, 'foo'));
    }

    /**
     * @test
     */
    public function is_empty(): void
    {
        $this->assertTrue($this->createWithItems(0)->isEmpty());
        $this->assertFalse($this->createWithItems(2)->isEmpty());
    }

    /**
     * @test
     */
    public function eager(): void
    {
        $this->assertEquals(
            [
                0 => $this->expectedValueAt(1),
                1 => $this->expectedValueAt(2),
            ],
            $this->createWithItems(2)->eager()->all()
        );
    }

    /**
     * @test
     */
    public function reduce(): void
    {
        $function = fn($carry, $value, $key) => $carry + $key;

        $this->assertNull($this->createWithItems(0)->reduce($function));
        $this->assertSame(10, $this->createWithItems(5)->reduce($function));
        $this->assertSame(15, $this->createWithItems(5)->reduce($function, 5));
    }

    /**
     * @test
     */
    public function sum(): void
    {
        $this->assertSame(0, $this->createWithItems(0)->sum(fn() => 10));
        $this->assertSame(10, $this->createWithItems(5)->sum(fn($value, $key) => $key));
        $this->assertSame(10.5, $this->createWithItems(5)->sum(fn($value, $key) => $key + 0.1));
    }

    abstract protected function createWithItems(int $count): Collection;
}
