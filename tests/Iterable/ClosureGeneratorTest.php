<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ClosureGeneratorTest extends IterableCollectionTest
{
    /**
     * @test
     */
    public function cannot_create_with_generator_directly(): void
    {
        $generator = static function() { yield 1; };

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$source must not be a generator directly as generators cannot be rewound. Try wrapping in a closure.');

        new IterableCollection($generator());
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

    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(static function() use ($count) {
            for ($i = 1; $i <= $count; ++$i) {
                yield $i;
            }
        });
    }
}
