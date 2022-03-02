<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\CallbackCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackCollectionTest extends TestCase
{
    use CollectionTests, PagintableCollectionTests;

    /**
     * @test
     */
    public function uses_count_callable_to_determine_count(): void
    {
        $collection = new CallbackCollection(fn() => [1, 2, 3, 4], fn() => 10);

        $this->assertCount(4, \iterator_to_array($collection));
        $this->assertCount(10, $collection);
    }

    protected function createWithItems(int $count): Collection
    {
        return new CallbackCollection(fn() => $count ? \range(1, $count) : [], fn() => $count);
    }
}
