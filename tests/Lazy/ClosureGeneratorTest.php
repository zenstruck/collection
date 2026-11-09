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
    public function is_empty_stops_at_the_first_item(): void
    {
        $collection = new LazyCollection(static function() {
            yield null;

            throw new \LogicException('The generator should not advance past the first item.');
        });

        $this->assertFalse($collection->isEmpty());
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
