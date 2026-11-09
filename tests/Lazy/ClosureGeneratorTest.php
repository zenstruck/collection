<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ClosureGeneratorTest extends LazyCollectionTest
{
    /**
     * @test
     */
    public function cannot_create_with_generator_directly(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$source must not be a generator directly as generators cannot be rewound. Try wrapping in a closure.');

        new LazyCollection((static fn() => yield 'foo' => 'bar')());
    }

    /**
     * @test
     */
    public function can_count_multiple_times(): void
    {
        $collection = $this->createWithItems(5);

        $this->assertCount(5, $collection);
        $this->assertCount(5, $collection);
    }

    /**
     * @test
     */
    public function take_opens_the_source_once_per_iteration(): void
    {
        $calls = 0;
        $collection = new LazyCollection(static function() use (&$calls) {
            ++$calls;

            return (static function() {
                yield 'skip' => 1;
                yield 'keep' => 2;

                throw new \LogicException('The generator should not advance past the slice.');
            })();
        });

        $slice = $collection->take(1, 1);

        $this->assertTrue($collection->take(0)->isEmpty());
        $this->assertSame(0, $calls);
        $this->assertSame(['keep' => 2], \iterator_to_array($slice));
        $this->assertSame(1, $calls);
        $this->assertSame(['keep' => 2], \iterator_to_array($slice));
        $this->assertSame(2, $calls);
    }

    /**
     * @test
     */
    public function is_empty_stops_at_the_first_item(): void
    {
        $collection = new LazyCollection(static function() {
            yield null;

            throw new \LogicException('The generator should not advance past the first item.');
        });

        $this->assertFalse($collection->isEmpty());
    }

    /**
     * @test
     */
    public function mapped_collection_checks_emptiness_without_mapping_or_counting(): void
    {
        $source = new LazyCollection(static function() {
            yield null;

            throw new \LogicException('The source should not advance past the first item.');
        });

        $mapped = $source->map(static fn() => throw new \LogicException('The mapper should not be called.'));

        $this->assertFalse($mapped->isEmpty());
    }

    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(static function() use ($count) {
            for ($i = 1; $i <= $count; ++$i) {
                yield $i;
            }
        });
    }
}
