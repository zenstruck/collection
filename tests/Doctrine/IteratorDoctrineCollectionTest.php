<?php

namespace Zenstruck\Collection\Tests\Doctrine;

use Zenstruck\Collection\DoctrineCollection;
use Zenstruck\Collection\Tests\DoctrineCollectionTest;
use Zenstruck\Collection\Tests\Fixture\Iterator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IteratorDoctrineCollectionTest extends DoctrineCollectionTest
{
    protected function createWithItems(int $count): DoctrineCollection
    {
        return new DoctrineCollection(new Iterator($count));
    }
}
