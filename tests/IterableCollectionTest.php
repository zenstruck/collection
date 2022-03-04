<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\IterableCollection;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class IterableCollectionTest extends TestCase
{
    use ExtraMethodsTests;

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

    abstract protected function createWithItems(int $count): IterableCollection;
}
