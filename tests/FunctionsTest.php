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

use Doctrine\Common\Collections\ArrayCollection as DoctrineArrayCollection;
use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\ArrayCollection;
use Zenstruck\Collection\Doctrine\DoctrineBridgeCollection;
use Zenstruck\Collection\LazyCollection;

use function Zenstruck\collect;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function collect(): void
    {
        $this->assertSame(['foo'], collect(['foo'])->eager()->all());
        $this->assertSame([], collect()->eager()->all());
        $this->assertSame([], collect(null)->eager()->all());
    }

    /**
     * @test
     */
    public function collect_arrays_as_array_collection(): void
    {
        $this->assertInstanceOf(ArrayCollection::class, collect([]));
        $this->assertInstanceOf(ArrayCollection::class, collect(['foo' => 'bar']));
    }

    /**
     * @test
     */
    public function collect_invokes_array_callables(): void
    {
        $this->assertInstanceOf(LazyCollection::class, collect([$this, 'items']));
        $this->assertSame(['foo' => 'bar'], collect([$this, 'items'])->eager()->all());

        $this->assertInstanceOf(LazyCollection::class, collect([self::class, 'staticItems']));
        $this->assertSame(['foo' => 'bar'], collect([self::class, 'staticItems'])->eager()->all());
    }

    /**
     * @test
     */
    public function collect_treats_non_callable_arrays_as_arrays(): void
    {
        $source = ['foo', 'bar'];

        $this->assertInstanceOf(ArrayCollection::class, collect($source));
        $this->assertSame($source, collect($source)->eager()->all());
    }

    /**
     * @test
     */
    public function collect_non_array_callables_as_lazy_collection(): void
    {
        $invokable = new class {
            public function __invoke(): iterable
            {
                return ['foo' => 'bar'];
            }
        };

        $this->assertInstanceOf(LazyCollection::class, collect(static fn() => ['foo' => 'bar']));
        $this->assertSame(['foo' => 'bar'], collect(static fn() => ['foo' => 'bar'])->eager()->all());

        $this->assertInstanceOf(LazyCollection::class, collect($invokable));
        $this->assertSame(['foo' => 'bar'], collect($invokable)->eager()->all());

        $this->assertInstanceOf(LazyCollection::class, collect('Zenstruck\Collection\Tests\items'));
        $this->assertSame(['foo' => 'bar'], collect('Zenstruck\Collection\Tests\items')->eager()->all());
    }

    /**
     * @test
     */
    public function collect_traversables_as_lazy_collection(): void
    {
        $collection = collect(new \ArrayIterator(['foo' => 'bar']));

        $this->assertInstanceOf(LazyCollection::class, $collection);
        $this->assertSame(['foo' => 'bar'], $collection->eager()->all());
    }

    /**
     * @test
     */
    public function collect_doctrine_collections_as_doctrine_bridge_collection(): void
    {
        $collection = collect(new DoctrineArrayCollection(['foo' => 'bar']));

        $this->assertInstanceOf(DoctrineBridgeCollection::class, $collection);
        $this->assertSame(['foo' => 'bar'], $collection->eager()->all());
    }

    /**
     * @test
     */
    public function collect_returns_collections_as_is(): void
    {
        $collection = ArrayCollection::for(['foo' => 'bar']);

        $this->assertSame($collection, collect($collection));
    }

    /**
     * @return array<string,string>
     */
    public function items(): array
    {
        return ['foo' => 'bar'];
    }

    /**
     * @return array<string,string>
     */
    public static function staticItems(): array
    {
        return ['foo' => 'bar'];
    }
}

function items(): iterable
{
    return ['foo' => 'bar'];
}
