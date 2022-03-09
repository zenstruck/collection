<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvokableObjectTest extends LazyCollectionTest
{
    protected function createWithItems(int $count): LazyCollection
    {
        return new LazyCollection(new class($count) {
            public function __construct(private $count)
            {
            }

            public function __invoke()
            {
                return $this->count ? \range(1, $this->count) : [];
            }
        });
    }
}
