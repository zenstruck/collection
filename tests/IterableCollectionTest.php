<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\IterableCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class IterableCollectionTest extends TestCase
{
    use CollectionTests, PagintableCollectionTests;

    /**
     * @test
     */
    public function to_array(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(\iterator_to_array($items), $items->toArray());
    }

    /**
     * @test
     */
    public function filter(): void
    {
        $items = $this->createWithItems(3);
        $arr = $items->toArray();

        $this->assertSame([1 => $arr[1], 2 => $arr[2]], $items->filter(fn($value, $key) => $key > 0)->toArray());
    }

    /**
     * @test
     */
    public function reject(): void
    {
        $items = $this->createWithItems(3);
        $arr = $items->toArray();

        $this->assertSame([0 => $arr[0]], $items->reject(fn($value, $key) => $key > 0)->toArray());
    }

    /**
     * @test
     */
    public function key_by(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(['k0', 'k1', 'k2'], \array_keys($items->keyBy(fn($value, $key) => 'k'.$key)->toArray()));
    }

    /**
     * @test
     */
    public function map(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(['v0', 'v1', 'v2'], $items->map(fn($value, $key) => 'v'.$key)->toArray());
    }

    /**
     * @test
     */
    public function map_with_keys(): void
    {
        $items = $this->createWithItems(3);

        $this->assertSame(
            ['k0' => 'v0', 'k1' => 'v1', 'k2' => 'v2'],
            $items->mapWithKeys(fn($value, $key) => yield 'k'.$key => 'v'.$key)->toArray()
        );
    }

    /**
     * @test
     */
    public function first(): void
    {
        $items = $this->createWithItems(2);

        $this->assertSame($items->toArray()[0], $items->first());
        $this->assertSame($items->toArray()[0], $items->first('foo'));
        $this->assertNull($this->createWithItems(0)->first());
        $this->assertSame('foo', $this->createWithItems(0)->first('foo'));
    }

    /**
     * @test
     */
    public function first_where(): void
    {
        $items = $this->createWithItems(2);

        $this->assertSame($items->toArray()[1], $items->firstWhere(fn($value, $key) => $key > 0));
        $this->assertSame($items->toArray()[1], $items->firstWhere(fn($value, $key) => $key > 0), 'foo');
        $this->assertNull($items->firstWhere(fn($value, $key) => $key > 10));
        $this->assertSame('foo', $items->firstWhere(fn($value, $key) => $key > 10, 'foo'));
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
        $items = $this->createWithItems(3);

        $this->assertSame($items->toArray(), $items->eager()->all());
    }

    abstract protected function createWithItems(int $count): IterableCollection;
}
