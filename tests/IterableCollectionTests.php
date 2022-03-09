<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Tests\Fixture\Stringable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @mixin TestCase
 */
trait IterableCollectionTests
{
    use PagintableCollectionTests;

    /**
     * @test
     */
    public function filter(): void
    {
        $items = $this->createWithItems(3);
        $arr = \iterator_to_array($items);

        $this->assertSame([1 => $arr[1], 2 => $arr[2]], \iterator_to_array($items->filter(fn($value, $key) => $key > 0)));
    }

    /**
     * @test
     */
    public function reject(): void
    {
        $items = $this->createWithItems(3);
        $arr = \iterator_to_array($items);

        $this->assertSame([0 => $arr[0]], \iterator_to_array($items->reject(fn($value, $key) => $key > 0)));
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

        $this->assertSame(\iterator_to_array($items)[0], $items->first());
        $this->assertSame(\iterator_to_array($items)[0], $items->first('foo'));
        $this->assertNull($this->createWithItems(0)->first());
        $this->assertSame('foo', $this->createWithItems(0)->first('foo'));
    }

    /**
     * @test
     */
    public function first_where(): void
    {
        $items = $this->createWithItems(2);

        $this->assertSame(\iterator_to_array($items)[1], $items->firstWhere(fn($value, $key) => $key > 0));
        $this->assertSame(\iterator_to_array($items)[1], $items->firstWhere(fn($value, $key) => $key > 0), 'foo');
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
        $this->assertSame(
            [
                0 => $this->expectedValueAt(1),
                1 => $this->expectedValueAt(2),
            ],
            $this->createWithItems(2)->eager()->all()
        );
    }
}
