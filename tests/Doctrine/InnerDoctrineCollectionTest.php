<?php

namespace Zenstruck\Collection\Tests\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Collection\DoctrineCollection;
use Zenstruck\Collection\Tests\DoctrineCollectionTest;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InnerDoctrineCollectionTest extends DoctrineCollectionTest
{
    protected function createWithItems(int $count): DoctrineCollection
    {
        return new DoctrineCollection(new ArrayCollection($count ? \range(1, $count) : []));
    }
}
