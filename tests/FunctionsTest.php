<?php

namespace Zenstruck\Collection\Tests;

use PHPUnit\Framework\TestCase;
use function Zenstruck\collect;

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
        $this->assertTrue(collect()->isEmpty());
    }
}
