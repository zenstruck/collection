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

        (new LazyCollection(static fn() => 'not iterable'))->count();
    }

    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(static fn() => new Iterator($count));
    }
}
