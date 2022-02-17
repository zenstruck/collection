<?php

namespace Zenstruck\Collection\Tests\Iterable;

use Zenstruck\Collection\IterableCollection;
use Zenstruck\Collection\Tests\IterableCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InvokableObjectTest extends IterableCollectionTest
{
    protected function createWithItems(int $count): IterableCollection
    {
        return new IterableCollection(new class($count) {
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
