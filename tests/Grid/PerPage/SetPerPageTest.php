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
use Zenstruck\Collection\Grid\PerPage\SetPerPage;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SetPerPageTest extends TestCase
{
    /**
     * @test
     */
    public function value(): void
    {
        $this->assertSame(20, (new SetPerPage())->value(null));
        $this->assertSame(20, (new SetPerPage())->value(20));
        $this->assertSame(20, (new SetPerPage())->value(21));
        $this->assertSame(50, (new SetPerPage())->value(50));
        $this->assertSame(100, (new SetPerPage())->value(100));
        $this->assertSame(20, (new SetPerPage())->value(1000));
    }
}
