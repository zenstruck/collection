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

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\CallbackCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallbackCollectionTest extends TestCase
{
    use CollectionTests;

    /**
     * @test
     */
    public function uses_count_callable_to_determine_count(): void
    {
        $collection = new CallbackCollection(static fn() => [1, 2, 3, 4], static fn() => 10);

        $this->assertCount(4, \iterator_to_array($collection));
        $this->assertCount(10, $collection);
    }

    protected function createWithItems(int $count): Collection
    {
        return new CallbackCollection(static fn() => $count ? \range(1, $count) : [], static fn() => $count);
    }
}
