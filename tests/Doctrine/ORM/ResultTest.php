<?php

namespace Zenstruck\Collection\Tests\Doctrine\ORM;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Doctrine\ORM\Result;
use Zenstruck\Collection\Tests\Doctrine\HasDatabase;
use Zenstruck\Collection\Tests\PagintableCollectionTests;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class ResultTest extends TestCase
{
    use HasDatabase, PagintableCollectionTests;

    /**
     * @test
     */
    public function can_get_first_result(): void
    {
        $result = $this->createWithItems(3);

        $this->assertEquals($this->expectedValueAt(1), $result->first());
    }

    /**
     * @test
     */
    public function first_returns_default_if_none(): void
    {
        $results = $this->createWithItems(0);

        $this->assertNull($results->first());
        $this->assertSame('default', $results->first('default'));
    }

    /**
     * @test
     */
    public function ensure_type_matches(): void
    {
        $results = $this->createWithItems(1);
        $expected = $this->expectedValueAt(1);

        if (\is_object($expected)) {
            $this->assertEquals($this->expectedValueAt(1), $results->first());
            $this->assertEquals([$expected], \iterator_to_array($results));
            $this->assertEquals([$expected], $results->toArray());

            return;
        }

        $this->assertSame($this->expectedValueAt(1), $results->first());
        $this->assertSame([$expected], \iterator_to_array($results));
        $this->assertSame([$expected], $results->toArray());
    }

    abstract protected function createWithItems(int $count): Result;
}
