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
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function first_returns_default_if_none(): void
    {
        $this->markTestIncomplete();
    }

    abstract protected function createWithItems(int $count): Result;
}
