<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\Fixture\Iterator;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ClosureIteratorTest extends IterableCollectionTest
{
    /**
     * @test
     */
    public function callable_must_be_iterable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$source callback must return iterable.');

        (new IterableCollection(fn() => 'not iterable'))->count();
    }

    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(fn() => new Iterator($count));
    }
}
