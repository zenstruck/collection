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
use Zenstruck\Collection\Grid\PerPage\FixedPerPage;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FixedPerPageTest extends TestCase
{
    /**
     * @test
     */
    public function value(): void
    {
        $this->assertSame(20, (new FixedPerPage(20))->value(null));
        $this->assertSame(20, (new FixedPerPage(20))->value(30));
    }
}
