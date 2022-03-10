<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use function Zenstruck\collect;
use function Zenstruck\arr;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FunctionsTest extends TestCase
{
    /**
     * @test
     */
    public function collect(): void
    {
        $this->assertSame(['foo'], collect(['foo'])->toArray());
    }

    /**
     * @test
     */
    public function map(): void
    {
        $this->assertSame(['foo'], arr(['foo'])->all());
    }
}
