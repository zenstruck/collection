<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection;
use Zenstruck\Collection\FactoryCollection;
use Zenstruck\Collection\LazyCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FactoryCollectionTest extends TestCase
{
    use CollectionTests;

    protected function createWithItems(int $count): Collection
    {
        return new FactoryCollection(new LazyCollection($count ? \range(1, $count) : []), fn($position) => "value {$position}");
    }

    protected function expectedValueAt(int $position): string
    {
        return "value {$position}";
    }
}
