<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\LazyCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class LazyCollectionTest extends TestCase
{
    use IterableCollectionTests;

    /**
     * @test
     */
    public function can_covert_to_array(): void
    {
        $this->assertSame(
            [
                0 => $this->expectedValueAt(1),
                1 => $this->expectedValueAt(2),
            ],
            $this->createWithItems(2)->toArray()
        );
    }

    abstract protected function createWithItems(int $count): LazyCollection;
}
