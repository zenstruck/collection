<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @mixin TestCase
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
trait CountableIteratorTests
{
    /**
     * @test
     */
    public function can_count(): void
    {
        $this->assertEmpty($this->createWithItems(0));
        $this->assertCount(4, $this->createWithItems(4));
    }

    /**
     * @test
     */
    public function can_iterate(): void
    {
        $mapped = [];

        foreach ($this->createWithItems(3) as $value) {
            $mapped[] = $value;
        }

        $this->assertCount(3, $mapped);
        $this->assertEquals($this->expectedValueAt(1), $mapped[0]);
        $this->assertEquals($this->expectedValueAt(2), $mapped[1]);
        $this->assertEquals($this->expectedValueAt(3), $mapped[2]);
    }

    protected function expectedValueAt(int $position)
    {
        return $position;
    }

    /**
     * @return \IteratorAggregate|\Countable
     */
    abstract protected function createWithItems(int $count);
}
