<?php

namespace Zenstruck\Collection\Tests\Doctrine\Collection;

use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Collection\Doctrine\CollectionDecorator;
use Zenstruck\Collection\Tests\Doctrine\CollectionDecoratorTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DoctrineCollectionCollectionDecoratorTest extends CollectionDecoratorTest
{
    protected function createWithItems(int $count): CollectionDecorator
    {
        return new CollectionDecorator(new ArrayCollection($count ? \range(1, $count) : []));
    }
}
