<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\Fixture\Iterator;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IteratorTest extends LazyCollectionTest
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

    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(new Iterator($count));
    }
}
