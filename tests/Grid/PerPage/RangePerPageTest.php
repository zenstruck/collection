<?php

/*
 * This file is part of the zenstruck/collection package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Collection\Tests\Grid\PerPage;

use PHPUnit\Framework\TestCase;
use Zenstruck\Collection\Grid\PerPage\RangePerPage;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class RangePerPageTest extends TestCase
{
    /**
     * @test
     */
    public function value(): void
    {
        $this->assertSame(20, (new RangePerPage())->value(null));
        $this->assertSame(30, (new RangePerPage())->value(30));
        $this->assertSame(100, (new RangePerPage())->value(200));
        $this->assertSame(90, (new RangePerPage())->value(90));
        $this->assertSame(1, (new RangePerPage())->value(-10));
    }
}
