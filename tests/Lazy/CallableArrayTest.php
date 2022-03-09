<?php

namespace Zenstruck\Collection\Tests\Lazy;

use Zenstruck\Collection\LazyCollection;
use Zenstruck\Collection\Tests\LazyCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CallableArrayTest extends LazyCollectionTest
{
    protected function createWithItems(int $count): LazyCollection
    {
        $object = new class($count) {
            public function __construct(private $count)
            {
            }

            public function values()
            {
                return $this->count ? \range(1, $this->count) : [];
            }
        };

        return new LazyCollection([$object, 'values']);
    }
}
