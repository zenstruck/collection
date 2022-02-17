<?php

namespace Zenstruck\Collection\Tests\Doctrine;

use Zenstruck\Collection\DoctrineCollection;
use Zenstruck\Collection\Tests\DoctrineCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ArrayDoctrineCollectionTest extends DoctrineCollectionTest
{
    protected function createWithItems(int $count): DoctrineCollection
    {
        return new DoctrineCollection($count ? \range(1, $count) : []);
    }
}
