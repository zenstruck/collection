<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\Fixture\Iterator;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ClosureIteratorTest extends LazyCollectionTest
{
    /**
     * @test
     */
    public function callable_must_be_iterable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$source callback must return iterable.');

        (new LazyCollection(fn() => 'not iterable'))->count();
    }

    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(fn() => new Iterator($count));
    }
}
