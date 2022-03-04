<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\ArrayCollection as Arr;
use Zenstruck\Collection\Tests\Fixture\Stringable;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayCollectionTest extends TestCase
{
    use ExtraMethodsTests;

    /**
     * @test
     */
    public function filter_no_callable(): void
    {
        $this->assertSame([], Arr::new([])->filter()->all());
        $this->assertSame([], Arr::new(['foo' => false, 'bar' => null, 'baz' => 0])->filter()->all());
        $this->assertSame(['bar' => 1], Arr::new(['foo' => false, 'bar' => 1, 'baz' => 0])->filter()->all());
    }

    /**
     * @test
     */
    public function reject_no_callable(): void
    {
        $this->assertSame([], Arr::new([])->reject()->all());
        $this->assertSame(['foo' => false, 'bar' => null, 'baz' => 0], Arr::new(['foo' => false, 'bar' => null, 'baz' => 0])->reject()->all());
        $this->assertSame([], Arr::new(['foo' => true, 'bar' => new \stdClass(), 'baz' => 1])->reject()->all());
    }

    /**
     * @test
     */
    public function eager(): void
    {
        $this->markTestSkipped('Does not have eager() method.');
    }

    /**
     * @test
     */
    public function all(): void
    {
        $this->assertSame(
            [
                0 => $this->expectedValueAt(1),
                1 => $this->expectedValueAt(2),
            ],
            $this->createWithItems(2)->all()
        );
    }

    /**
     * @test
     */
    public function keys(): void
    {
        $this->assertSame(
            [
                0,
                1,
            ],
            $this->createWithItems(2)->keys()->all()
        );
    }

    /**
     * @test
     */
    public function values(): void
    {
        $this->assertSame([0, 1], Arr::new(['foo' => 0, 'bar' => 1])->values()->all());
    }

    /**
     * @test
     */
    public function reverse(): void
    {
        $this->assertSame(
            [
                1 => $this->expectedValueAt(2),
                0 => $this->expectedValueAt(1),
            ],
            $this->createWithItems(2)->reverse()->all()
        );
    }

    /**
     * @test
     */
    public function slice(): void
    {
        $collection = Arr::new(['a', 'b', 'c', 'd', 'e']);

        $this->assertSame(['a', 'b'], $collection->slice(0, -3)->all());
        $this->assertSame([2 => 'c', 3 => 'd'], $collection->slice(-3, 2)->all());
    }

    /**
     * @test
     */
    public function merge(): void
    {
        $this->assertSame(['foo', 'bar', 'baz', 'foo'], Arr::new(['foo'])->merge(['bar'], new \ArrayIterator(['baz']), ['foo'])->all());
        $this->assertSame(['foo' => 1, 'bar' => 3], Arr::new(['foo' => 2])->merge(['foo' => 1], ['bar' => 1], ['bar' => 3])->all());
    }

    /**
     * @test
     */
    public function sort(): void
    {
        $this->assertSame([0, 1, 2], Arr::new([2, 1, 0])->sort()->values()->all());
        $this->assertEquals([new \DateTime('2022-01-01'), new \DateTime('2022-01-02'), new \DateTime('2022-01-03')], Arr::new([new \DateTime('2022-01-03'), new \DateTime('2022-01-01'), new \DateTime('2022-01-02')])->sort()->values()->all());
    }

    /**
     * @test
     */
    public function sort_desc(): void
    {
        $this->assertSame([2, 1, 0], Arr::new([2, 1, 0])->sortDesc()->values()->all());
        $this->assertEquals([new \DateTime('2022-01-03'), new \DateTime('2022-01-02'), new \DateTime('2022-01-01')], Arr::new([new \DateTime('2022-01-03'), new \DateTime('2022-01-01'), new \DateTime('2022-01-02')])->sortDesc()->values()->all());
    }

    /**
     * @test
     */
    public function sort_by(): void
    {
        $this->assertSame([0, 1, 2], Arr::new([2, 1, 0])->sortBy(fn($v) => $v)->values()->all());
        $this->assertEquals([new \DateTime('2022-01-01'), new \DateTime('2022-01-02'), new \DateTime('2022-01-03')], Arr::new([new \DateTime('2022-01-03'), new \DateTime('2022-01-01'), new \DateTime('2022-01-02')])->sortBy(fn($v) => $v)->values()->all());
    }

    /**
     * @test
     */
    public function sort_by_desc(): void
    {
        $this->assertSame([2, 1, 0], Arr::new([2, 1, 0])->sortByDesc(fn($v) => $v)->values()->all());
        $this->assertEquals([new \DateTime('2022-01-03'), new \DateTime('2022-01-02'), new \DateTime('2022-01-01')], Arr::new([new \DateTime('2022-01-03'), new \DateTime('2022-01-01'), new \DateTime('2022-01-02')])->sortByDesc(fn($v) => $v)->values()->all());
    }

    /**
     * @test
     */
    public function sort_keys(): void
    {
        $this->assertSame([0 => null, 1 => null, 2 => null], Arr::new([2 => null, 1 => null, 0 => null])->sortKeys()->all());
    }

    /**
     * @test
     */
    public function sort_keys_desc(): void
    {
        $this->assertSame([2 => null, 1 => null, 0 => null], Arr::new([2 => null, 1 => null, 0 => null])->sortKeysDesc()->all());
    }

    /**
     * @test
     */
    public function combine(): void
    {
        $this->assertSame(
            [
                'foo' => 1,
                'bar' => 2,
            ],
            Arr::new(['foo', 'bar'])->combine([1, 2])->all()
        );
    }

    /**
     * @test
     */
    public function combine_with_self(): void
    {
        $this->assertSame(
            [
                'foo' => 'foo',
                'bar' => 'bar',
            ],
            Arr::new(['foo', 'bar'])->combineWithSelf()->all()
        );
    }

    /**
     * @test
     */
    public function group_by(): void
    {
        $arr = Arr::new(
            [
                $first = ['name' => 'kevin', 'country' => 'CA'],
                $second = ['name' => 'ryan', 'country' => new Stringable('US')],
                $third = ['name' => 'leanna', 'country' => 'US'],
            ]
        );

        $this->assertSame([
            'CA' => [
                $first,
            ],
            'US' => [
                $second,
                $third,
            ],
        ], $arr->groupBy(fn($v) => $v['country'])->all());
    }

    /**
     * @test
     */
    public function get(): void
    {
        $items = Arr::new([0 => 5, 'foo' => 'bar', 'baz' => null]);

        $this->assertSame(5, $items->get(0));
        $this->assertSame('bar', $items->get('foo'));
        $this->assertNull($items->get('baz'));
        $this->assertNull($items->get('baz', 'default'));
        $this->assertNull($items->get('invalid'));
        $this->assertSame('default', $items->get('invalid', 'default'));
    }

    /**
     * @test
     */
    public function set(): void
    {
        $items = Arr::new([0 => 5]);

        $this->assertSame([0 => 10, 'foo' => 'bar'], $items->set(0, 10)->set('foo', 'bar')->all());
    }

    /**
     * @test
     */
    public function unset(): void
    {
        $this->assertSame(['foo' => null], Arr::new(['foo' => null, 'bar' => null, 'baz' => null])->unset('bar', 'baz')->all());
    }

    /**
     * @test
     */
    public function only(): void
    {
        $this->assertSame(['bar' => null, 'baz' => null], Arr::new(['foo' => null, 'bar' => null, 'baz' => null])->only('bar', 'baz')->all());
    }

    /**
     * @test
     */
    public function push(): void
    {
        $this->assertSame([0, 1, 5, 10], Arr::new([0])->push(1, 5, 10)->all());
    }

    /**
     * @test
     */
    public function in(): void
    {
        $items = Arr::new([0 => 1, 'foo' => 'bar']);

        $this->assertTrue($items->in(1));
        $this->assertTrue($items->in('bar'));
        $this->assertFalse($items->in('foo'));
    }

    /**
     * @test
     */
    public function key_exists(): void
    {
        $items = Arr::new([0 => 1, 'foo' => 'bar', 'baz' => null]);

        $this->assertTrue($items->keyExists(0));
        $this->assertTrue($items->keyExists('foo'));
        $this->assertTrue($items->keyExists('baz'));
        $this->assertFalse($items->keyExists(1));
    }

    /**
     * @test
     */
    public function implode(): void
    {
        $this->assertSame('foo.bar', Arr::new(['foo', 'bar'])->implode('.'));
    }

    /**
     * @test
     */
    public function array_accessor(): void
    {
        $items = Arr::new([0 => 5]);

        $items[0] = 10;
        $items['foo'] = 'bar';
        $items[] = 'baz';

        $this->assertSame([0 => 10, 'foo' => 'bar', 1 => 'baz'], $items->all());
        $this->assertSame('bar', $items['foo']);
        $this->assertTrue(isset($items['foo']));
        $this->assertFalse(isset($items['bar']));
    }

    /**
     * @test
     */
    public function explode_constructor(): void
    {
        $this->assertSame(['foo', 'bar', 'baz'], Arr::explode('.', 'foo.bar.baz')->all());
        $this->assertSame(['foo', 'bar.baz'], Arr::explode('.', 'foo.bar.baz', 2)->all());
        $this->assertSame([], Arr::explode('.', '')->all());
    }

    /**
     * @test
     */
    public function range_constructor(): void
    {
        $this->assertSame([0, 1, 2], Arr::range(0, 2)->all());
        $this->assertSame(['a', 'b', 'c'], Arr::range('a', 'c')->all());
        $this->assertSame([0, 2, 4, 6, 8, 10], Arr::range(0, 10, 2)->all());
    }

    /**
     * @test
     */
    public function fill_constructor(): void
    {
        $this->assertSame([2 => null, 3 => null, 4 => null], Arr::fill(2, 3, null)->all());
    }

    protected function createWithItems(int $count): Arr
    {
        return new Arr($count ? \range(1, $count) : []);
    }
}
