<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\Fixture\Iterator;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IteratorTest extends IterableCollectionTest
{
    /**
     * @test
     */
    public function take_page_limit_cannot_be_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createWithItems(1)->take(-1);
    }

    /**
     * @test
     */
    public function take_page_offset_cannot_be_negative(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->createWithItems(1)->take(5, -1);
    }

    /**
     * @test
     */
    public function non_callable_source_must_be_iterable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$source must be callable, iterable or null.');

        new IterableCollection('not iterable');
    }

    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(new Iterator($count));
    }
}
