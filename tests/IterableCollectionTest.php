<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\IterableCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class IterableCollectionTest extends TestCase
{
    use PagintableCollectionTests;

    abstract protected function createWithItems(int $count): IterableCollection;
}
