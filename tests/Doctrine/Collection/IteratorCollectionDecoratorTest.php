<?php

namespace Zenstruck\Collection\Tests\Doctrine\Collection;

use Zenstruck\Collection\Doctrine\CollectionDecorator;
use Zenstruck\Collection\Tests\Doctrine\CollectionDecoratorTest;
use Zenstruck\Collection\Tests\Fixture\Iterator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IteratorCollectionDecoratorTest extends CollectionDecoratorTest
{
    protected function createWithItems(int $count): CollectionDecorator
    {
        return new CollectionDecorator(new Iterator($count));
    }
}
